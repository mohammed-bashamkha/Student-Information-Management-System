<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        AcademicYear::factory()->createMany([
            ['year' => '2022-2023'],
            ['year' => '2023-2024'],
            ['year' => '2024-2025'],
            ['year' => '2025-2026'],
            ['year' => '2026-2027'],
        ]);

        User::factory()->create([
            'name' => 'Mohammed Faiz Bashamkha',
            'email' => 'mohammed.bashamkha@gmail.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin'
        ]);

        $user_id = User::first('id');

        Level::factory()->createMany([
            ['created_by' => $user_id,'name' => 'أساسي'],
            ['created_by' => $user_id,'name' => 'ثانوي'],
        ]);

        SchoolClass::factory()->createMany([
            ['created_by' => $user_id,'name' => 'الأول', 'level_id' => 1],
            ['created_by' => $user_id,'name' => 'الثاني', 'level_id' => 1],
            ['created_by' => $user_id,'name' => 'الثالث', 'level_id' => 1],
            ['created_by' => $user_id,'name' => 'الرابع', 'level_id' => 1],
            ['created_by' => $user_id,'name' => 'الخامس', 'level_id' => 1],
            ['created_by' => $user_id,'name' => 'السادس', 'level_id' => 1],
            ['created_by' => $user_id,'name' => 'السابع', 'level_id' => 1],
            ['created_by' => $user_id,'name' => 'الثامن', 'level_id' => 1],
            ['created_by' => $user_id,'name' => 'التاسع', 'level_id' => 1],

            ['created_by' => $user_id,'name' => 'أولى ثانوي', 'level_id' => 2],
            ['created_by' => $user_id,'name' => 'ثانية ثانوي علمي', 'level_id' => 2],
            ['created_by' => $user_id,'name' => 'ثانية ثانوي أدبي', 'level_id' => 2],
            ['created_by' => $user_id,'name' => 'ثالثة ثانوي علمي', 'level_id' => 2],
            ['created_by' => $user_id,'name' => 'ثالثة ثانوي أدبي', 'level_id' => 2],
        ]);

        Subject::factory()->createMany([
            ['created_by' => $user_id, 'level_id' => 1, 'name' => 'القران الكريم'],
            ['created_by' => $user_id, 'level_id' => 1, 'name' => 'التربية الأسلامية'],
            ['created_by' => $user_id, 'level_id' => 1, 'name' => 'اللغة العربية'],
            ['created_by' => $user_id, 'level_id' => 1, 'name' => 'اللغة الإنجليزية'],
            ['created_by' => $user_id, 'level_id' => 1, 'name' => 'الرياضيات'],
            ['created_by' => $user_id, 'level_id' => 1, 'name' => 'الاجتماعيات'],
            ['created_by' => $user_id, 'level_id' => 1, 'name' => 'العلوم'],
            ['created_by' => $user_id, 'level_id' => 1, 'name' => 'التربية الفنية'],
            ['created_by' => $user_id, 'level_id' => 1, 'name' => 'الحاسوب'],
            ['created_by' => $user_id, 'level_id' => 1, 'name' => 'التربية بدنية'],

            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'اللغة العربية'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'القران الكريم'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'التربية الأسلامية'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'اللغة العربية'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'اللغة الإنجليزية'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'الرياضيات'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'الفيزياء'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'الكيمياء'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'الأحياء'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'التاريخ'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'الجغرافيا'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'أقتصاد'],
            ['created_by' => $user_id, 'level_id' => 2, 'name' => 'مجتمع'],
        ]);
    }
}
