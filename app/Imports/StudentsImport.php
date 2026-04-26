<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\StudentEnrollment;
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
use Carbon\Carbon;

class StudentsImport implements ToCollection, WithStartRow, WithEvents, WithChunkReading
{
    protected $schoolId, $classId, $academicYearId, $userId;
    public $preview = false;
    public $previewData = [];

    public $stats = [
        'total_rows'       => 0,
        'successful'       => 0,
        'failed'           => 0,
        'skipped'          => 0,
        'students_created' => 0,
        'students_updated' => 0,
        'errors'           => [],
        'warnings'         => [],
    ];

    public function __construct($schoolId, $classId, $academicYearId)
    {
        $this->schoolId = $schoolId;
        $this->classId = $classId;
        $this->academicYearId = $academicYearId;
        $this->userId = Auth::id() ?? 1;
    }

    /**
     * يبدأ القراءة من الصف 12
     */
    public function startRow(): int
    {
        return 12;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function collection(Collection $rows)
    {
        $this->stats['total_rows'] += $rows->count();

        // 1. استخراج الأرقام المدرسية في هذا الـ Chunk
        $schoolNumbers = $rows->map(function ($row) {
            return $this->sanitizeValue($row[1]);
        })->filter()->unique()->toArray();

        // 2. تحميل الطلاب الموجودين مسبقاً دفعة واحدة
        $existingStudents = Student::with(['enrollments' => function ($q) {
            $q->where('academic_year_id', $this->academicYearId);
        }, 'transfers' => function ($q) {
            $q->where('status', 'active');
        }])
            ->whereIn('school_number', $schoolNumbers)
            ->get()
            ->keyBy('school_number');

        DB::transaction(function () use ($rows, &$existingStudents) {
            foreach ($rows as $rowIndex => $row) {
                $actualRowNumber = $rowIndex + $this->startRow();

                try {
                    // تخطي الصفوف إذا كان الرقم المدرسي والاسم فارغين
                    if (empty($row[1]) && empty($row[2])) continue;

                    $processed = $this->processRow($row, $actualRowNumber, $existingStudents);
                    if ($processed) {
                        $this->stats['successful']++;
                    }
                } catch (\Exception $e) {
                    $this->stats['failed']++;
                    $this->stats['errors'][] = [
                        'row'     => $actualRowNumber,
                        'message' => $e->getMessage(),
                        'name'    => $row[2] ?? 'غير معروف'
                    ];

                    Log::error("خطأ في الاستيراد - صف {$actualRowNumber}: " . $e->getMessage());
                }
            }
        });
    }

    private function processRow(Collection $row, int $rowNumber, &$existingStudents): bool
    {
        /**
         * ترتيب الأعمدة بناءً على التصدير الأخير (10 أعمدة):
         * 0 => م (مسلسل)
         * 1 => الرقم المدرسي
         * 2 => الاسم الكامل
         * 3 => رقم الجلوس
         * 4 => الجنسية
         * 5 => الجنس (ذكر/أنثى)
         * 6 => تاريخ الميلاد
         * 7 => مكان الميلاد
         * 8 => تاريخ التسجيل
         * 9 => تاريخ الإنشاء (يُهمل في الاستيراد)
         */

        $schoolNumber   = $this->sanitizeValue($row[1]);
        $rawFullName    = $this->sanitizeValue($row[2]);
        $seatNumber     = $this->sanitizeValue($row[3]);
        $nationality    = $this->sanitizeValue($row[4]) ?? 'يمني';
        $genderText     = $this->sanitizeValue($row[5]);
        $dob            = $row[6];
        $placeOfBirth   = $this->sanitizeValue($row[7]);
        $regDate        = $row[8];

        // 1. تنظيف الاسم من المسافات الزائدة
        $fullName = trim(preg_replace('/\s+/', ' ', $rawFullName ?? ''));

        // 2. التحقق من البيانات
        if (!$schoolNumber) throw new \Exception("الرقم المدرسي مفقود.");
        if (!$fullName) throw new \Exception("اسم الطالب مفقود.");

        // 2b. تخطي الطالب إذا كان موقوفاً أو لديه تحويل باستخدام البيانات المحملة مسبقاً
        $existingForCheck = $existingStudents->get($schoolNumber);
        if ($existingForCheck) {
            $isSuspendedInThisYear = $existingForCheck->enrollments
                ->where('status', 'suspended')
                ->isNotEmpty();

            if ($isSuspendedInThisYear) {
                $this->stats['skipped']++;
                $this->stats['warnings'][] = "الطالب [{$fullName}] (رقم: {$schoolNumber}) موقوف - تم تخطيه.";
                return false;
            }

            // 2c. تخطي الطالب إذا كان لديه تحويل أو قبول مؤقت مفعل في مدرسة أخرى
            $activeTransfer = $existingForCheck->transfers->first();

            if ($activeTransfer && $activeTransfer->to_school_id != $this->schoolId) {
                $this->stats['skipped']++;
                $typeLabel = $activeTransfer->type === 'transfer' ? 'تحويل' : 'قبول مؤقت';
                $this->stats['warnings'][] = "الطالب [{$fullName}] (رقم: {$schoolNumber}) يمتلك {$typeLabel} مفعل في مدرسة أخرى - تم تخطيه.";
                return false;
            }
        }

        // التحقق من أن الاسم رباعي (يحتوي على 3 مسافات على الأقل)
        if (!preg_match('/^(\S+\s){3,}\S+$/u', $fullName)) {
            throw new \Exception("الاسم [" . $fullName . "] يجب أن يكون رباعياً على الأقل (3 مسافات).");
        }

        $gender = $this->mapGender($genderText, $rowNumber);

        // 3. إنشاء أو تحديث بيانات الطالب الأساسية
        if ($existingForCheck) {
            if (!$this->preview) {
                $existingForCheck->update([
                    'full_name'         => $fullName,
                    'seat_number'       => $seatNumber,
                    'nationality'       => $nationality,
                    'gender'            => $gender,
                    'date_of_birth'     => $this->transformDate($dob),
                    'place_of_birth'    => $placeOfBirth,
                    'registration_date' => $this->transformDate($regDate) ?? now(),
                ]);
            }

            $studentId = $existingForCheck->id;
            $this->stats['students_updated']++;
        } else {
            if (!$this->preview) {
                $newStudent = Student::create([
                    'school_number'     => $schoolNumber,
                    'full_name'         => $fullName,
                    'seat_number'       => $seatNumber,
                    'nationality'       => $nationality,
                    'gender'            => $gender,
                    'date_of_birth'     => $this->transformDate($dob),
                    'place_of_birth'    => $placeOfBirth,
                    'registration_date' => $this->transformDate($regDate) ?? now(),
                    'created_by'        => $this->userId,
                ]);
                $studentId = $newStudent->id;
            } else {
                $newStudent = new Student(['school_number' => $schoolNumber, 'full_name' => $fullName]);
                $newStudent->id = rand(1000000, 9999999);
                $studentId = $newStudent->id;
            }

            $this->stats['students_created']++;

            // إضافة الطالب الجديد للاستعلامات اللاحقة في نفس الـ Chunk
            $existingStudents->put($schoolNumber, $newStudent);
        }

        // 4. ربط الطالب بالسنة والدراسة (Enrollment)
        if (!$this->preview) {
            StudentEnrollment::updateOrCreate(
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
        }

        if ($this->preview && count($this->previewData) < 5) {
            $this->previewData[] = [
                'school_number' => $schoolNumber,
                'full_name'     => $fullName,
                'nationality'   => $nationality,
                'status'        => $existingForCheck ? 'تحديث' : 'طالب جديد'
            ];
        }

        return true;
    }

    private function mapGender($value, $rowNumber)
    {
        $value = trim($value);
        if ($value === 'ذكر') return 'male';
        if ($value === 'أنثى') return 'female';

        throw new \Exception("قيمة الجنس غير صحيحة (يجب ذكر أو أنثى). القيمة المكتوبة: [{$value}]");
    }

    private function sanitizeValue($value): ?string
    {
        return $value ? trim((string)$value) : null;
    }

    private function transformDate($value)
    {
        if (!$value) return null;

        try {
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                Log::info('بدء عملية استيراد الطلاب.');
            },
            AfterImport::class => function (AfterImport $event) {
                Log::info('انتهت عملية الاستيراد.', $this->stats);
            },
        ];
    }
}
