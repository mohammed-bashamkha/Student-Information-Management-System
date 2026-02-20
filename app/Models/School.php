<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function students() {
        return $this->hasMany(Student::class);
    }

    public function certificateReplacements() {
        return $this->hasMany(CertificateReplacement::class);
    }

    public function transfersFrom() {
        return $this->hasMany(TransfersAdmission::class, 'from_school_id');
    }

    public function transfersTo() {
        return $this->hasMany(TransfersAdmission::class, 'to_school_id');
    }
}
