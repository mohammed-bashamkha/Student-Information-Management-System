<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_id = User::first('id');

        Level::factory()->createMany([
            ['created_by' => $user_id,'name' => 'أساسي'],
            ['created_by' => $user_id,'name' => 'ثانوي'],
        ]);
    }
}
