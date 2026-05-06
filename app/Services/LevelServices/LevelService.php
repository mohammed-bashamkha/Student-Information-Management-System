<?php

namespace App\Services\LevelServices;

use App\Models\Level;

class LevelService
{
    public function getLevels()
    {
        $levels = Level::all();
        return $levels;
    }
}
