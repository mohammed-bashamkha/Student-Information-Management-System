<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method \Illuminate\Database\Eloquent\Relations\HasMany subjects()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany students()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany enrollments()
 */
class SchoolClass extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'school_class_id');
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function certificateReplacements()
    {
        return $this->hasMany(CertificateReplacement::class);
    }
}
