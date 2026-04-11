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
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Carbon\Carbon;

class StudentsImport implements ToCollection, WithStartRow, WithEvents
{
    protected $schoolId, $classId, $academicYearId, $userId;

    public $stats = [
        'total_rows'       => 0,
        'successful'       => 0,
        'failed'           => 0,
        'students_created' => 0,
        'students_updated' => 0,
        'errors'           => [],
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

    public function collection(Collection $rows)
    {
        $this->stats['total_rows'] = $rows->count();

        DB::transaction(function () use ($rows) {
            foreach ($rows as $rowIndex => $row) {
                $actualRowNumber = $rowIndex + $this->startRow();
                
                try {
                    // تخطي الصفوف إذا كان الرقم المدرسي والاسم فارغين
                    if (empty($row[1]) && empty($row[2])) continue;

                    $this->processRow($row, $actualRowNumber);
                    $this->stats['successful']++;

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

    private function processRow(Collection $row, int $rowNumber)
    {
        /**
         * ترتيب الأعمدة بناءً على التصدير الأخير (9 أعمدة):
         * 0 => م (مسلسل)
         * 1 => الرقم المدرسي
         * 2 => الاسم الكامل
         * 3 => رقم الجلوس
         * 4 => الجنسية
         * 5 => الجنس (ذكر/أنثى)
         * 6 => تاريخ الميلاد
         * 7 => تاريخ التسجيل
         * 8 => تاريخ الإنشاء (يُهمل في الاستيراد)
         */

        $schoolNumber   = $this->sanitizeValue($row[1]);
        $rawFullName    = $this->sanitizeValue($row[2]);
        $seatNumber     = $this->sanitizeValue($row[3]);
        $nationality    = $this->sanitizeValue($row[4]) ?? 'يمني';
        $genderText     = $this->sanitizeValue($row[5]);
        $dob            = $row[6];
        $regDate        = $row[7];

        // 1. تنظيف الاسم من المسافات الزائدة
        $fullName = trim(preg_replace('/\s+/', ' ', $rawFullName ?? ''));

        // 2. التحقق من البيانات
        if (!$schoolNumber) throw new \Exception("الرقم المدرسي مفقود.");
        if (!$fullName) throw new \Exception("اسم الطالب مفقود.");
        
        // التحقق من أن الاسم رباعي (يحتوي على 3 مسافات على الأقل)
        if (!preg_match('/^(\S+\s){3,}\S+$/u', $fullName)) {
            throw new \Exception("الاسم [" . $fullName . "] يجب أن يكون رباعياً على الأقل (3 مسافات).");
        }
        
        $gender = $this->mapGender($genderText, $rowNumber);

        // 3. إنشاء أو تحديث بيانات الطالب الأساسية
        $existingStudent = Student::where('school_number', $schoolNumber)->first();
        $isNew = !$existingStudent;

        $student = Student::updateOrCreate(
            ['school_number' => $schoolNumber],
            [
                'full_name'         => $fullName,
                'seat_number'       => $seatNumber,
                'nationality'       => $nationality,
                'gender'            => $gender,
                'date_of_birth'     => $this->transformDate($dob),
                'registration_date' => $this->transformDate($regDate) ?? now(),
                'updated_at'        => now(),
            ]
        );

        // تعيين المنشئ فقط في حالة الطالب الجديد
        if ($isNew) {
            $student->update(['created_by' => $this->userId]);
            $this->stats['students_created']++;
        } else {
            $this->stats['students_updated']++;
        }

        // 4. ربط الطالب بالسنة والدراسة (Enrollment)
        // يتم التحديث أو الإنشاء بناءً على الطالب والسنة الدراسية
        StudentEnrollment::updateOrCreate(
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