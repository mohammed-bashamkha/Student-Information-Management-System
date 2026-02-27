<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Error extends Model
{
    protected $guarded = [];

    public function incorrectStudent()
    {
        return $this->belongsTo(Student::class,'incorrect_student_id');
    }
    public function correctStudent()
    {
        return $this->belongsTo(Student::class,'correct_student_id');
    }
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class,'class_id');
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class);
    }
}
