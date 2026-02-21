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
            ['year' => '2022-2023'],
            ['year' => '2023-2024'],
            ['year' => '2024-2025'],
            ['year' => '2025-2026'],
            ['year' => '2026-2027'],
            ['year' => '2027-2028'],
            ['year' => '2028-2029'],
            ['year' => '2029-2030'],
        ]);
    }
}
