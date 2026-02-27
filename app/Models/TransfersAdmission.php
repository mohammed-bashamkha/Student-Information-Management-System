<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransfersAdmission extends Model
{
    protected $guarded = [];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function fromSchool() {
        return $this->belongsTo(School::class, 'from_school_id');
    }

    public function toSchool() {
        return $this->belongsTo(School::class, 'to_school_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
