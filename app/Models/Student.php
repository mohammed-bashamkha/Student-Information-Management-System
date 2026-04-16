<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function finalResult()
    {
        return $this->hasOne(FinalResult::class);
    }

    public function transfers()
    {
        return $this->hasMany(TransfersAdmission::class);
    }

    public function certificateReplacements()
    {
        return $this->hasMany(CertificateReplacement::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function currentEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)->latestOfMany();
    }

    // =========================================================
    // Scopes
    // =========================================================

    /**
     * استثناء الطلاب الموقوفين من أي استعلام.
     * الاستخدام: Student::notSuspended()->get()
     */
    public function scopeNotSuspended($query)
    {
        return $query->whereHas('currentEnrollment', function ($q) {
            $q->where('status', '!=', 'suspended');
        });
    }

    // =========================================================
    // Helpers
    // =========================================================

    /**
     * هل الطالب موقوف حالياً؟
     * الاستخدام: $student->isSuspended()
     */
    public function isSuspended(): bool
    {
        return $this->currentEnrollment?->status === 'suspended';
    }
}
