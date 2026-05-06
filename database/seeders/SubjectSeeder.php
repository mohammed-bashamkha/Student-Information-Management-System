<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    protected function createSubjectsWithClasses($classIds, $subjects, $level_id, $user_id)
    {
        foreach ($subjects as $subjectName) {
            
            $subject = Subject::firstOrCreate([
                'name'     => $subjectName,
                'level_id' => $level_id,
            ], [
                'created_by' => $user_id,
            ]);

            // ربطها مع الفصول
            $subject->schoolClasses()->syncWithoutDetaching($classIds);
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

        // جلب IDs فقط
        $classesFrom1To2 = SchoolClass::where('level_id', 1)
            ->whereIn('id', [1, 2])->pluck('id')->toArray();

        $classesFrom3To6 = SchoolClass::where('level_id', 1)
            ->whereIn('id', [3,4,5,6])->pluck('id')->toArray();

        $classesFrom7To9 = SchoolClass::where('level_id', 1)
            ->whereIn('id', [7,8,9])->pluck('id')->toArray();

        $firstSecondaryGrade = SchoolClass::where('level_id', 2)
            ->whereIn('id', [10])->pluck('id')->toArray();

        $SecondSecondaryGradeScientific = SchoolClass::where('level_id', 2)
            ->whereIn('id', [11,13])->pluck('id')->toArray();

        $SecondSecondaryGradeLiterary = SchoolClass::where('level_id', 2)
            ->whereIn('id', [12,14])->pluck('id')->toArray();

        // التنفيذ
        $this->createSubjectsWithClasses($classesFrom1To2, $firstEducationSubjects, 1, $user_id);

        $this->createSubjectsWithClasses($classesFrom3To6, $secondEducationSubjects, 1, $user_id);

        $this->createSubjectsWithClasses($classesFrom7To9, $thirdEducationSubjects, 1, $user_id);

        $this->createSubjectsWithClasses($firstSecondaryGrade, $fourthEducationSubjects, 2, $user_id);

        $this->createSubjectsWithClasses($SecondSecondaryGradeScientific, $fifthEducationSubjects, 2, $user_id);

        $this->createSubjectsWithClasses($SecondSecondaryGradeLiterary, $SixthEducationSubjects, 2, $user_id);
    }
}