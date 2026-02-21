<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_id = User::first('id');

        School::factory()->createMany([
            ['name' => 'الشهيدة سمية', 'address' => 'حضرموت - المكلا', 'created_by' => $user_id],
            ['name' => 'ابن خلدون', 'address' => 'حضرموت - المكلا', 'created_by' => $user_id],
            ['name' => 'السعد العامرية', 'address' => 'حضرموت - المكلا', 'created_by' => $user_id],
        ]);
    }
}
