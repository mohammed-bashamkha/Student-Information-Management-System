<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function students() {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function level() {
        return $this->belongsTo(Level::class);
    }

    public function subjects() {
        return $this->hasMany(Subject::class, 'school_class_id');
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }
}
