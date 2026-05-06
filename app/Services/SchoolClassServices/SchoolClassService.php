<?php

namespace App\Services\SchoolClassServices;

use App\Models\SchoolClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class SchoolClassService
{
    use AuthorizesRequests;
    public function getSchoolClasses()
    {
        $this->authorize('viewAny', SchoolClass::class);
        $schoolClasses = SchoolClass::with('level')->get();
        return $schoolClasses;
    }

    public function storeSchoolClass(array $validateData)
    {
        $this->authorize('manageSchoolClass', SchoolClass::class);
        $validateData['created_by'] = Auth::id();
        $schoolClass = SchoolClass::create($validateData);
        return $schoolClass;
    }

    public function updateSchoolClass(array $validateData, string $id)
    {
        $schoolClass = SchoolClass::findOrFail($id);
        $this->authorize('manageSchoolClass', $schoolClass);
        $validateData['created_by'] = Auth::id();
        $schoolClass->update($validateData);
        return $schoolClass;
    }

    public function deleteSchoolClass(string $id)
    {
        $schoolClass = SchoolClass::findOrFail($id);
        $this->authorize('manageSchoolClass', $schoolClass);
        $schoolClass->delete();
        return $schoolClass;
    }
}
