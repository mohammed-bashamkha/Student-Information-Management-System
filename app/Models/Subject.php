<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function grades() {
        return $this->hasMany(Grade::class);
    }

    public function level() {
        return $this->belongsTo(Level::class);
    }

    public function schoolClasses() {
        return $this->belongsToMany(SchoolClass::class, 'subject_school_class');
    }
}
