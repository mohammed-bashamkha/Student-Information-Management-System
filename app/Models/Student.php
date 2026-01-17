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

    public function level() {
        return $this->belongsTo(Level::class);
    }
}
