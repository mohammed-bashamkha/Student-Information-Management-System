<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_id = User::first('id');

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
    }
}
