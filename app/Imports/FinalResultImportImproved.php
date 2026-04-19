<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Grade;
use App\Models\FinalResult;
use App\Models\SchoolClass;
use App\Models\StudentEnrollment;
use App\Services\ResultCalculationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;

/**
 * نسخة محسّنة لاستيراد النتائج النهائية مع معالجة أخطاء أفضل وتقارير مفصلة
 */
class FinalResultImportImproved implements ToCollection, WithStartRow, WithEvents, WithChunkReading
{
    protected $academicYearId;
    protected $classId;
    protected $schoolId;
    protected $userId;

    private $subjectsCache = [];

    // إحصائيات الاستيراد
    public $stats = [
        'total_rows'          => 0,
        'successful'          => 0,
        'failed'              => 0,
        'skipped'             => 0,
        'students_created'    => 0,
        'students_updated'    => 0,
        'enrollments_created' => 0,
        'errors'              => [],
        'warnings'            => []
    ];

    public function __construct(int $academicYearId, int $classId, int $schoolId)
    {
        $this->academicYearId = $academicYearId;
        $this->classId = $classId;
        $this->schoolId = $schoolId;
        $this->userId = Auth::id() ?? 1;
    }

    public function startRow(): int
    {
        return 13;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function collection(Collection $rows)
    {
        // التحقق من وجود المواد قبل البدء
        $subjects = $this->getSubjectsForClass($this->classId);
        if ($subjects->isEmpty()) {
            throw new \Exception("لا توجد مواد مسجلة لهذا الصف. الرجاء التأكد من تسجيل المواد أولاً.");
        }

        $this->stats['total_rows'] += $rows->count();

        // استخراج جميع الأرقام المدرسية
        $schoolNumbers = $rows->map(function ($row) {
            return $this->sanitizeValue($row[1] ?? null);
        })->filter()->unique()->toArray();

        // تحميل الطلاب دفعة واحدة مع علاقاتهم
        $existingStudents = Student::with(['enrollments' => function ($q) {
            $q->where('academic_year_id', $this->academicYearId);
        }, 'transfers' => function ($q) {
            $q->where('status', 'active');
        }])
            ->whereIn('school_number', $schoolNumbers)
            ->get()
            ->keyBy('school_number');

        DB::transaction(function () use ($rows, $subjects, &$existingStudents) {
            $gradesToUpsert = [];
            $finalResultsToUpsert = [];
            $studentsToCalculate = [];

            foreach ($rows as $rowIndex => $row) {
                $actualRowNumber = $rowIndex + $this->startRow();

                try {
                    $this->processRowAndCollect($row, $subjects, $actualRowNumber, $existingStudents, $gradesToUpsert, $finalResultsToUpsert, $studentsToCalculate);
                    $this->stats['successful']++;
                } catch (\Exception $e) {
                    $this->stats['failed']++;
                    $this->stats['errors'][] = [
                        'row' => $actualRowNumber,
                        'message' => $e->getMessage(),
                        'data' => [
                            'student_number' => $row[1] ?? 'N/A',
                            'student_name' => $row[2] ?? 'N/A'
                        ]
                    ];

                    Log::error("خطأ في استيراد النتائج - الصف {$actualRowNumber}", [
                        'error' => $e->getMessage(),
                        'row_data' => $row->toArray()
                    ]);
                }
            }

            // تنفيذ عمليات الإدراج أو التحديث المجمعة Bulk Upsert
            if (!empty($gradesToUpsert)) {
                Grade::upsert(
                    $gradesToUpsert,
                    ['student_id', 'subject_id', 'academic_year_id'],
                    ['school_id', 'class_id', 'first_semester_total', 'second_semester_total', 'total', 'updated_at', 'created_by']
                );
            }

            if (!empty($finalResultsToUpsert)) {
                FinalResult::upsert(
                    $finalResultsToUpsert,
                    ['student_id', 'academic_year_id'],
                    ['total_student_grades', 'average_grade', 'final_result', 'notes', 'updated_at', 'created_by']
                );
            }

            // تحديث النتائج التلقائية بعد إدراج كل الدرجات
            if (!empty($studentsToCalculate)) {
                $calcService = new ResultCalculationService();
                foreach ($studentsToCalculate as $studentId) {
                    $calcService->calculateFinalResult($studentId, $this->academicYearId, $this->userId);
                }
            }
        });
    }

    /**
     * معالجة صف واحد من البيانات وجمع البيانات للإدراج المجمع
     */
    private function processRowAndCollect(Collection $row, Collection $subjects, int $rowNumber, &$existingStudents, array &$gradesToUpsert, array &$finalResultsToUpsert, array &$studentsToCalculate)
    {
        // استخراج البيانات الأساسية
        $studentNumber = $this->sanitizeValue($row[1] ?? null);
        $studentName = $this->sanitizeValue($row[2] ?? null);

        // التحقق من البيانات الأساسية
        $this->validateStudentData($studentNumber, $studentName, $rowNumber);

        // التحقق المسبق: هل الطالب موقوف أو محول
        $existingForCheck = $existingStudents->get($studentNumber);
        if ($existingForCheck) {
            $isSuspended = $existingForCheck->enrollments->where('status', 'suspended')->isNotEmpty();
            if ($isSuspended) {
                $this->stats['skipped']++;
                $this->stats['warnings'][] = "الطالب [{$studentName}] (رقم: {$studentNumber}) في الصف {$rowNumber} موقوف - تم تخطيه.";
                return;
            }

            $activeTransfer = $existingForCheck->transfers->first();
            if ($activeTransfer && $activeTransfer->to_school_id != $this->schoolId) {
                $this->stats['skipped']++;
                $typeLabel = $activeTransfer->type === 'transfer' ? 'تحويل' : 'قبول مؤقت';
                $this->stats['warnings'][] = "الطالب [{$studentName}] (رقم: {$studentNumber}) في الصف {$rowNumber} يمتلك {$typeLabel} مفعل في مدرسة أخرى - تم تخطيه.";
                return;
            }
        }

        // إنشاء أو تحديث الطالب
        if ($existingForCheck) {
            $existingForCheck->update([
                'full_name'  => $studentName,
            ]);
            $studentId = $existingForCheck->id;
            $this->stats['students_updated']++;
        } else {
            $newStudent = Student::create([
                'school_number' => $studentNumber,
                'full_name'  => $studentName,
                'created_by' => $this->userId,
            ]);
            $studentId = $newStudent->id;
            $this->stats['students_created']++;
            $existingStudents->put($studentNumber, $newStudent);
        }

        // تسجيل الطالب (Enrollment)
        $enrollment = StudentEnrollment::updateOrCreate(
            [
                'student_id'       => $studentId,
                'academic_year_id' => $this->academicYearId,
            ],
            [
                'school_id'  => $this->schoolId,
                'class_id'   => $this->classId,
                'created_by' => $this->userId,
            ]
        );

        if ($enrollment->wasRecentlyCreated) {
            $this->stats['enrollments_created']++;
        }

        // معالجة درجات المواد
        $this->collectSubjectGrades($studentId, $studentName, $subjects, $row, $rowNumber, $gradesToUpsert);

        // معالجة النتيجة النهائية
        $this->collectFinalResult($studentId, $subjects, $row, $rowNumber, $finalResultsToUpsert, $studentsToCalculate);
    }

    /**
     * التحقق من صحة بيانات الطالب
     */
    private function validateStudentData($studentNumber, $studentName, int $rowNumber)
    {
        if (empty($studentNumber)) {
            throw new \Exception("الرقم المدرسي مفقود في الصف {$rowNumber}");
        }

        if (empty($studentName)) {
            throw new \Exception("اسم الطالب مفقود في الصف {$rowNumber}");
        }

        // التحقق من صحة الرقم المدرسي
        if (!is_numeric($studentNumber)) {
            $this->stats['warnings'][] = "الرقم المدرسي غير رقمي في الصف {$rowNumber}: {$studentNumber}";
        }
    }

    /**
     * تجميع درجات المواد للإدراج المجمع
     */
    private function collectSubjectGrades(int $studentId, string $studentName, Collection $subjects, Collection $row, int $rowNumber, array &$gradesToUpsert)
    {
        $subjectGradesStartIndex = 3;

        foreach ($subjects as $index => $subject) {
            $firstSemesterIndex = $subjectGradesStartIndex + ($index * 3);

            $firstSemesterGrade = $this->parseGrade($row[$firstSemesterIndex] ?? null);
            $secondSemesterGrade = $this->parseGrade($row[$firstSemesterIndex + 1] ?? null);
            $totalGrade = $this->parseGrade($row[$firstSemesterIndex + 2] ?? null);

            if ($totalGrade === null && ($firstSemesterGrade !== null || $secondSemesterGrade !== null)) {
                $totalGrade = ($firstSemesterGrade ?? 0) + ($secondSemesterGrade ?? 0);
            }

            // التحقق من المجموع
            if ($totalGrade !== null && $firstSemesterGrade !== null && $secondSemesterGrade !== null) {
                $calculatedTotal = $firstSemesterGrade + $secondSemesterGrade;
                if (abs($calculatedTotal - $totalGrade) > 0.5) {
                    $this->stats['warnings'][] = sprintf(
                        "تحذير: مجموع درجات المادة '%s' للطالب '%s' في الصف %d غير متطابق (محسوب: %.1f، مسجل: %.1f)",
                        $subject->name,
                        $studentName,
                        $rowNumber,
                        $calculatedTotal,
                        $totalGrade
                    );
                }
            }

            if ($firstSemesterGrade !== null || $secondSemesterGrade !== null || $totalGrade !== null) {
                $gradesToUpsert[] = [
                    'student_id'            => $studentId,
                    'subject_id'            => $subject->id,
                    'academic_year_id'      => $this->academicYearId,
                    'school_id'             => $this->schoolId,
                    'class_id'              => $this->classId,
                    'first_semester_total'  => $firstSemesterGrade,
                    'second_semester_total' => $secondSemesterGrade,
                    'total'                 => $totalGrade,
                    'created_by'            => $this->userId,
                    'created_at'            => now()->toDateTimeString(),
                    'updated_at'            => now()->toDateTimeString(),
                ];
            }
        }
    }

    /**
     * تجميع النتيجة النهائية للإدراج المجمع
     */
    private function collectFinalResult(int $studentId, Collection $subjects, Collection $row, int $rowNumber, array &$finalResultsToUpsert, array &$studentsToCalculate)
    {
        $finalResultStartIndex = 3 + (count($subjects) * 3);

        $totalStudentGrades = $this->parseGrade($row[$finalResultStartIndex] ?? null);
        $gpa = $this->sanitizeValue($row[$finalResultStartIndex + 1] ?? null);
        $finalResult = $this->sanitizeValue($row[$finalResultStartIndex + 2] ?? null);
        $notes = $this->sanitizeValue($row[$finalResultStartIndex + 3] ?? null);

        if ($totalStudentGrades === null || $gpa === null || $finalResult === null) {
            $studentsToCalculate[] = $studentId;
        } else {
            $finalResultsToUpsert[] = [
                'student_id'           => $studentId,
                'academic_year_id'     => $this->academicYearId,
                'total_student_grades' => $totalStudentGrades,
                'average_grade'        => $gpa,
                'final_result'         => $finalResult,
                'notes'                => $notes,
                'created_by'           => $this->userId,
                'created_at'           => now()->toDateTimeString(),
                'updated_at'           => now()->toDateTimeString(),
            ];
        }
    }

    /**
     * تنظيف القيمة النصية
     */
    private function sanitizeValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    /**
     * تحويل الدرجة إلى رقم
     */
    private function parseGrade($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // تنظيف القيمة
        $cleaned = trim((string) $value);

        // التحقق من أنها رقم
        if (!is_numeric($cleaned)) {
            return null;
        }

        return (float) $cleaned;
    }

    /**
     * الحصول على مواد الصف مع Cache
     */
    private function getSubjectsForClass(int $classId): Collection
    {
        if (isset($this->subjectsCache[$classId])) {
            return $this->subjectsCache[$classId];
        }

        $class = SchoolClass::find($classId);
        if (!$class) {
            return collect();
        }

        $subjects = $class->subjects()->orderBy('id', 'asc')->get();

        return $this->subjectsCache[$classId] = $subjects;
    }

    /**
     * الحصول على تقرير الاستيراد
     */
    public function getImportReport(): array
    {
        return [
            'summary' => [
                'total_rows'          => $this->stats['total_rows'],
                'successful'          => $this->stats['successful'],
                'failed'              => $this->stats['failed'],
                'skipped'             => $this->stats['skipped'],
                'students_created'    => $this->stats['students_created'],
                'students_updated'    => $this->stats['students_updated'],
                'enrollments_created' => $this->stats['enrollments_created'],
                'success_rate'        => $this->stats['total_rows'] > 0
                    ? round(($this->stats['successful'] / $this->stats['total_rows']) * 100, 2)
                    : 0
            ],
            'errors'   => $this->stats['errors'],
            'warnings' => $this->stats['warnings']
        ];
    }

    /**
     * التسجيل قبل وبعد الاستيراد
     */
    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                Log::info('بدء استيراد النتائج النهائية', [
                    'academic_year_id' => $this->academicYearId,
                    'class_id' => $this->classId,
                    'school_id' => $this->schoolId,
                    'user_id' => $this->userId
                ]);
            },

            AfterImport::class => function (AfterImport $event) {
                Log::info('انتهاء استيراد النتائج النهائية', $this->getImportReport());
            },
        ];
    }
}
