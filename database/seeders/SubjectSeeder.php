<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    private array $subjectsToInsert = [];

    protected function subjectLoops($schoolClass, $educationSubjects, $level_id, $user_id)
    {
        foreach ($schoolClass as $classId) {
            foreach ($educationSubjects as $subjectName) {
                $this->subjectsToInsert[] = [
                    'created_by'      => $user_id,
                    'level_id'        => $level_id,
                    'school_class_id' => $classId,
                    'name'            => $subjectName,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }
    }

    public function run(): void
    {
        $user_id = User::first()->id;

        $firstEducationSubjects = [
            'القران الكريم', 'التربية الأسلامية', 
            'اللغة العربية','الرياضيات', 'العلوم'
        ];

        $secondEducationSubjects = [
            'القران الكريم', 'التربية الأسلامية', 
            'اللغة العربية', 'الرياضيات', 'الاجتماعيات', 'العلوم'
        ];

        $thirdEducationSubjects = [
            'القران الكريم', 'التربية الأسلامية', 
            'اللغة العربية', 'اللغة الإنجليزية',
            'الرياضيات', 'الاجتماعيات', 'العلوم'
        ];

        $thirdEducationSubjects = [
            'القران الكريم', 'التربية الأسلامية', 
            'اللغة العربية', 'اللغة الإنجليزية',
            'الرياضيات', 'الاجتماعيات', 'العلوم'
        ];

        $fourthEducationSubjects = [
            'القران الكريم', 'التربية الأسلامية', 
            'اللغة العربية', 'اللغة الإنجليزية',
            'الرياضيات', 'الفيزياء', 'الكيمياء',
            'الاحياء', 'الجغرافيا', 'التاريخ', 'المجتمع المدني'
        ];
        
        $fifthEducationSubjects = [
            'القران الكريم', 'التربية الأسلامية', 
            'اللغة العربية', 'اللغة الإنجليزية',
            'الرياضيات', 'الفيزياء', 'الكيمياء', 'الاحياء'
        ];
        
        $SixthEducationSubjects = [
            'القران الكريم', 'التربية الأسلامية', 
            'اللغة العربية', 'اللغة الإنجليزية','الرياضيات',
            'الجغرافيا', 'التاريخ', 'مبادئ اقتصاد', 'علوم الاجتماع'
        ];
        
        $classesFrom1To2 = SchoolClass::where('level_id', 1)
        ->whereIn('id', [1, 2])->pluck('id');

        $classesFrom3To6 = SchoolClass::where('level_id', 1)
            ->whereIn('id', [3,4,5,6])->pluck('id');

        $classesFrom7To9 = SchoolClass::where('level_id', 1)
            ->whereIn('id', [7,8,9])->pluck('id');

        $firstSecondaryGrade = SchoolClass::where('level_id', 2)
            ->whereIn('id', [10])->pluck('id');

        $SecondSecondaryGradeScientific = SchoolClass::where('level_id', 2)
            ->whereIn('id', [11,13])->pluck('id');

        $SecondSecondaryGradeLiterary = SchoolClass::where('level_id', 2)
            ->whereIn('id', [12,14])->pluck('id');

        $this->subjectLoops($classesFrom1To2, $firstEducationSubjects, 1, $user_id);

        $this->subjectLoops($classesFrom3To6, $secondEducationSubjects, 1, $user_id);

        $this->subjectLoops($classesFrom7To9, $thirdEducationSubjects, 1, $user_id);

        $this->subjectLoops($firstSecondaryGrade, $fourthEducationSubjects, 2, $user_id);

        $this->subjectLoops($SecondSecondaryGradeScientific, $fifthEducationSubjects, 2, $user_id);

        $this->subjectLoops($SecondSecondaryGradeLiterary, $SixthEducationSubjects, 2, $user_id);

        if (!empty($this->subjectsToInsert)) {
            Subject::insert($this->subjectsToInsert);
        }
    }
}
