<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_id = User::first('id');
        
        Subject::factory()->createMany([
            ['created_by' => $user_id, 'level_id' => 1, 'school_class_id'=> 1, 'name' => 'القران الكريم'],
            ['created_by' => $user_id, 'level_id' => 1, 'school_class_id'=> 1, 'name' => 'التربية الأسلامية'],
            ['created_by' => $user_id, 'level_id' => 1, 'school_class_id'=> 1, 'name' => 'اللغة العربية'],
            ['created_by' => $user_id, 'level_id' => 1, 'school_class_id'=> 1, 'name' => 'اللغة الإنجليزية'],
            ['created_by' => $user_id, 'level_id' => 1, 'school_class_id'=> 1, 'name' => 'الرياضيات'],
            ['created_by' => $user_id, 'level_id' => 1, 'school_class_id'=> 1, 'name' => 'الاجتماعيات'],
            ['created_by' => $user_id, 'level_id' => 1, 'school_class_id'=> 1, 'name' => 'العلوم'],
            ['created_by' => $user_id, 'level_id' => 1, 'school_class_id'=> 1, 'name' => 'التربية الفنية'],
            ['created_by' => $user_id, 'level_id' => 1, 'school_class_id'=> 1, 'name' => 'الحاسوب'],
            ['created_by' => $user_id, 'level_id' => 1, 'school_class_id'=> 1, 'name' => 'التربية بدنية'],

            ['created_by' => $user_id, 'level_id' => 2, 'school_class_id'=> 10, 'name' => 'القران الكريم'],
            ['created_by' => $user_id, 'level_id' => 2, 'school_class_id'=> 10, 'name' => 'اللغة العربية'],
            ['created_by' => $user_id, 'level_id' => 2, 'school_class_id'=> 10, 'name' => 'اللغة الإنجليزية'],
            ['created_by' => $user_id, 'level_id' => 2, 'school_class_id'=> 10, 'name' => 'الرياضيات'],
            ['created_by' => $user_id, 'level_id' => 2, 'school_class_id'=> 10, 'name' => 'الفيزياء'],
            ['created_by' => $user_id, 'level_id' => 2, 'school_class_id'=> 10, 'name' => 'الكيمياء'],
            ['created_by' => $user_id, 'level_id' => 2, 'school_class_id'=> 10, 'name' => 'الأحياء'],
            ['created_by' => $user_id, 'level_id' => 2, 'school_class_id'=> 10, 'name' => 'التاريخ'],
            ['created_by' => $user_id, 'level_id' => 2, 'school_class_id'=> 10, 'name' => 'الجغرافيا'],
            ['created_by' => $user_id, 'level_id' => 2, 'school_class_id'=> 10, 'name' => 'أقتصاد'],
            ['created_by' => $user_id, 'level_id' => 2, 'school_class_id'=> 10, 'name' => 'مجتمع'],
        ]);
    }
}
