<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicYear::factory()->createMany([
            ['year' => '2022/2023', 'start_date'=>'2022-07-03', 'end_date'=>'2023-04-10'],
            ['year' => '2023/2024', 'start_date'=>'2023-07-03', 'end_date'=>'2024-04-10'],
            ['year' => '2024/2025', 'start_date'=>'2024-07-03', 'end_date'=>'2025-04-10'],
            ['year' => '2025/2026', 'start_date'=>'2025-07-03', 'end_date'=>'2026-04-10', 'status'=>'active'],
        ]);
    }
}
