<?php

namespace App\Services\AcademicYearServices;

use App\Models\AcademicYear;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AcademicYearService
{
    use AuthorizesRequests;
    public function getAcademicYears()
    {
        $academicYears = AcademicYear::latest()->get();
        return $academicYears;
    }

    public function createAcademicYear($validateData)
    {
        $this->authorize('manageAcademicYear');

        if (isset($validateData['status']) && $validateData['status'] === 'active') {
            AcademicYear::query()->where('status', 'active')->update(['status' => 'inactive']);
        }

        $academicYear = AcademicYear::create($validateData);
        return $academicYear;
    }

    public function updateAcademicYear($validateData, $id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        $this->authorize('manageAcademicYear', $academicYear);

        if (isset($validateData['status']) && $validateData['status'] === 'active') {
            AcademicYear::query()->where('id', '!=', $id)->where('status', 'active')->update(['status' => 'inactive']);
        }

        $academicYear->update($validateData);
        return $academicYear;
    }

    public function deleteAcademicYear($id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        $this->authorize('manageAcademicYear', $academicYear);
        $academicYear->delete();
        return $academicYear;
    }
}