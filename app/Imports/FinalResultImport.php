<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Grade;
use App\Models\FinalResult;
use App\Models\SchoolClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\DB;

class FinalResultImport implements ToCollection, WithStartRow
{
    protected $academicYearId;
    protected $classId;
    protected $schoolId;
    protected $userId;

    private $subjectsCache = [];

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
        DB::transaction(function () use ($rows) {
            $subjects = $this->getSubjectsForClass($this->classId);
            if ($subjects->isEmpty()) {
                throw new \Exception("لا توجد مواد مسجلة لهذا الصف. الرجاء التأكد من تسجيل المواد أولاً.");
            }

            foreach ($rows as $row) {
                // --- التصحيح الأول هنا ---
                $studentNumber = $row[1] ?? null; // الرقم المدرسي في العمود الأول
                $studentName   = $row[2] ?? null; // اسم الطالب في العمود الثاني

                if (empty($studentNumber) || empty($studentName)) {
                    continue;
                }

                $student = Student::updateOrCreate(
                    ['school_number' => $studentNumber],
                    [
                        'full_name' => $studentName,
                        'school_id' => $this->schoolId,
                        'class_id' => $this->classId, // <-- التصحيح الثاني هنا
                        'created_by' => $this->userId
                    ]
                );

                // هذا صحيح، درجات المواد تبدأ من العمود الثالث
                $subjectGradesStartIndex = 3;

                foreach ($subjects as $index => $subject) {
                    $firstSemesterIndex = $subjectGradesStartIndex + ($index * 3);
                    Grade::updateOrCreate(
                        ['student_id' => $student->id, 'subject_id' => $subject->id, 'academic_year_id' => $this->academicYearId],
                        [
                            'first_semester_total' => $row[$firstSemesterIndex] ?? null,
                            'second_semester_total' => $row[$firstSemesterIndex + 1] ?? null,
                            'total' => $row[$firstSemesterIndex + 2] ?? null,
                            'created_by' => $this->userId
                        ]
                    );
                }

                $finalResultStartIndex = $subjectGradesStartIndex + (count($subjects) * 3);

                FinalResult::updateOrCreate(
                    ['student_id' => $student->id, 'academic_year_id' => $this->academicYearId],
                    [
                        'total_student_grades' => $row[$finalResultStartIndex] ?? 0,
                        'final_result' => $row[$finalResultStartIndex + 1] ?? 'N/A',
                        'notes' => $row[$finalResultStartIndex + 2] ?? null,
                        'created_by' => $this->userId
                    ]
                );
            }
        });
    }

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
}
