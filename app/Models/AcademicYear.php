<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $guarded = [];

    public function grades() {
        return $this->hasMany(Grade::class);
    }

    public function finalResults() {
        return $this->hasMany(FinalResult::class);
    }
}
