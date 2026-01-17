<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FinalResultExport implements FromCollection, WithHeadings
{
    protected $className;
    protected $academicYearId;
    protected $subjects;

    public function __construct(string $className, int $academicYearId)
    {
        $this->className = $className;
        $this->academicYearId = $academicYearId;

        // جلب مواد الصف (عن طريق المستوى)
        $this->subjects = Subject::whereHas('level.classes', function ($q) {
            $q->where('name', $this->className);
        })->orderBy('id')->get();
    }

    /**
     * العناوين (ديناميكية)
     */
    public function headings(): array
    {
        $headings = [
            'Level',
            'Class',
            'Student Name',
            'Student Number',
        ];

        foreach ($this->subjects as $subject) {
            $headings[] = $subject->name . ' T1';
            $headings[] = $subject->name . ' T2';
            $headings[] = $subject->name . ' Total';
        }

        $headings[] = 'Total Result';
        $headings[] = 'Final Result';
        $headings[] = 'Notes';

        return $headings;
    }

    /**
     * البيانات
     */
    public function collection()
    {
        $students = Student::whereHas('schoolClass', function ($q) {
            $q->where('name', $this->className);
        })->with([
            'schoolClass.level',
            'grades' => function ($q) {
                $q->where('academic_year_id', $this->academicYearId);
            },
            'finalResult' => function ($q) {
                $q->where('academic_year_id', $this->academicYearId);
            }
        ])->orderBy('full_name')->get();

        $rows = collect();

        foreach ($students as $student) {
            $row = [
                $student->schoolClass->level->name,
                $student->schoolClass->name,
                $student->full_name,
                $student->school_number,
            ];

            foreach ($this->subjects as $subject) {
                $grade = $student->grades
                    ->firstWhere('subject_id', $subject->id);

                $row[] = $grade->first_semester_total ?? '';
                $row[] = $grade->second_semester_total ?? '';
                $row[] = $grade->total ?? '';
            }

            $row[] = optional($student->finalResult)->total_student_grades;
            $row[] = optional($student->finalResult)->final_result;
            $row[] = optional($student->finalResult)->notes;

            $rows->push($row);
        }

        return $rows;
    }
}
