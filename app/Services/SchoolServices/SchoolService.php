<?php

namespace App\Services\SchoolServices;

use App\Models\School;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class SchoolService
{
    use AuthorizesRequests;
    public function getSchools(?string $search = null, ?string $schoolType = null)
    {
        $this->authorize('viewAny', School::class);
        
        $query = School::withCount('enrollments');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($schoolType) {
            $query->where('school_type', $schoolType);
        }

        $schools = $query->get()
            ->map(function ($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'school_type' => $school->school_type,
                    'capacity' => $school->capacity,
                    'current_students' => $school->enrollments_count,
                    'address' => $school->address,
                    'density_percentage' => $school->capacity > 0 ? round(($school->enrollments_count / $school->capacity) * 100) : 0
                ];
            });
            
        return $schools;
    }

    public function getSchool(string $id)
    {
        $school = School::findOrFail($id);
        $this->authorize('view', $school);
        return $school;
    }

    public function storeSchool(array $validateData)
    {
        $this->authorize('create', School::class);
        $validateData['created_by'] = Auth::id();
        $school = School::create($validateData);
        return $school;
    }

    public function updateSchool(array $validateData, string $id)
    {
        $school = School::findOrFail($id);
        $this->authorize('update', $school);
        // Note: created_by should probably not be updated here if it's meant to be the creator
        // but I'll keep the logic if that's what's intended for 'updated_by'
        $school->update($validateData);
        return $school;
    }

    public function deleteSchool(string $id)
    {
        $school = School::withCount('enrollments')->findOrFail($id);
        $this->authorize('delete', $school);

        if ($school->enrollments_count > 0) {
            throw new \Exception('لا يمكن حذف المدرسة لوجود طلاب مسجلين بها', 403);
        }

        $school->delete();
        return $school;
    }
}
