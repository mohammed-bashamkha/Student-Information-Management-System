<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    public function grades() {
        return $this->hasMany(Grade::class);
    }

    public function school() {
        return $this->belongsTo(School::class);
    }

    public function schoolClass() {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function finalResult() {
        return $this->hasOne(FinalResult::class);
    }

    public function transfers() {
        return $this->hasMany(TransfersAdmission::class);
    }

    public function certificateReplacements() {
        return $this->hasMany(CertificateReplacement::class);
    }
}
