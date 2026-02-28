<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Grade;
use App\Models\FinalResult;
use App\Models\SchoolClass;
use App\Models\StudentEnrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;

/**
 * نسخة محسّنة لاستيراد النتائج النهائية مع معالجة أخطاء أفضل وتقارير مفصلة
 */
class FinalResultImportImproved implements ToCollection, WithStartRow, WithEvents
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
        return 5;
    }

    public function collection(Collection $rows)
    {
        // التحقق من وجود المواد قبل البدء
        $subjects = $this->getSubjectsForClass($this->classId);
        if ($subjects->isEmpty()) {
            throw new \Exception("لا توجد مواد مسجلة لهذا الصف. الرجاء التأكد من تسجيل المواد أولاً.");
        }

        $this->stats['total_rows'] = $rows->count();

        DB::transaction(function () use ($rows, $subjects) {
            foreach ($rows as $rowIndex => $row) {
                $actualRowNumber = $rowIndex + $this->startRow();

                try {
                    $this->processRow($row, $subjects, $actualRowNumber);
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

                    // تسجيل الخطأ
                    Log::error("خطأ في استيراد النتائج - الصف {$actualRowNumber}", [
                        'error' => $e->getMessage(),
                        'row_data' => $row->toArray()
                    ]);
                }
            }
        });
    }

    /**
     * معالجة صف واحد من البيانات
     */
    private function processRow(Collection $row, Collection $subjects, int $rowNumber)
    {
        // استخراج البيانات الأساسية
        $studentNumber = $this->sanitizeValue($row[1] ?? null);
        $studentName = $this->sanitizeValue($row[2] ?? null);

        // التحقق من البيانات الأساسية
        $this->validateStudentData($studentNumber, $studentName, $rowNumber);

        // البحث عن الطالب أو إنشاؤه
        $student = $this->findOrCreateStudent($studentNumber, $studentName);

        // معالجة درجات المواد
        $this->processSubjectGrades($student, $subjects, $row, $rowNumber);

        // معالجة النتيجة النهائية
        $this->processFinalResult($student, $subjects, $row, $rowNumber);
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
     * البحث عن الطالب أو إنشاؤه
     */
    private function findOrCreateStudent(string $studentNumber, string $studentName): Student
    {
        $existingStudent = Student::where('school_number', $studentNumber)->first();
        $isNew = !$existingStudent;

        $student = Student::updateOrCreate(
            ['school_number' => $studentNumber],
            [
                'full_name'  => $studentName,
                'created_by' => $this->userId,
            ]
        );

        if ($isNew) {
            $this->stats['students_created']++;
        } else {
            $this->stats['students_updated']++;
        }

        // إنشاء أو تحديث سجل التسجيل في الصف والسنة الدراسية
        $enrollment = StudentEnrollment::updateOrCreate(
            [
                'student_id'       => $student->id,
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

        return $student;
    }

    /**
     * معالجة درجات المواد
     */
    private function processSubjectGrades(Student $student, Collection $subjects, Collection $row, int $rowNumber)
    {
        $subjectGradesStartIndex = 3;

        foreach ($subjects as $index => $subject) {
            $firstSemesterIndex = $subjectGradesStartIndex + ($index * 3);

            $firstSemesterGrade = $this->parseGrade($row[$firstSemesterIndex] ?? null);
            $secondSemesterGrade = $this->parseGrade($row[$firstSemesterIndex + 1] ?? null);
            $totalGrade = $this->parseGrade($row[$firstSemesterIndex + 2] ?? null);

            // التحقق من المجموع
            if ($totalGrade !== null && $firstSemesterGrade !== null && $secondSemesterGrade !== null) {
                $calculatedTotal = $firstSemesterGrade + $secondSemesterGrade;
                if (abs($calculatedTotal - $totalGrade) > 0.5) {
                    $this->stats['warnings'][] = sprintf(
                        "تحذير: مجموع درجات المادة '%s' للطالب '%s' في الصف %d غير متطابق (محسوب: %.1f، مسجل: %.1f)",
                        $subject->name,
                        $student->full_name,
                        $rowNumber,
                        $calculatedTotal,
                        $totalGrade
                    );
                }
            }

            Grade::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $this->academicYearId
                ],
                [
                    'first_semester_total' => $firstSemesterGrade,
                    'second_semester_total' => $secondSemesterGrade,
                    'total' => $totalGrade,
                    'created_by' => $this->userId
                ]
            );
        }
    }

    /**
     * معالجة النتيجة النهائية
     */
    private function processFinalResult(Student $student, Collection $subjects, Collection $row, int $rowNumber)
    {
        $finalResultStartIndex = 3 + (count($subjects) * 3);

        $totalStudentGrades = $this->parseGrade($row[$finalResultStartIndex] ?? null) ?? 0;
        $finalResult = $this->sanitizeValue($row[$finalResultStartIndex + 1] ?? null);
        $notes = $this->sanitizeValue($row[$finalResultStartIndex + 2] ?? null);

        FinalResult::updateOrCreate(
            [
                'student_id' => $student->id,
                'academic_year_id' => $this->academicYearId
            ],
            [
                'total_student_grades' => $totalStudentGrades,
                'final_result' => $finalResult,
                'notes' => $notes,
                'created_by' => $this->userId
            ]
        );
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
