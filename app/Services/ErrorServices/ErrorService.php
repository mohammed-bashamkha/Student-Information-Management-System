<?php

namespace App\Services\ErrorServices;

use App\Models\Error;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ErrorService
{
    use AuthorizesRequests;
    public function getErrors(array $filters = [])
    { 
        $this->authorize('viewAny', Error::class);

        $query = Error::with([
            'student', 'createdBy', 
            'academicYear', 'schoolClass', 'school']);

        if ($filters['search']) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('student', function ($subQ) use ($searchTerm) {
                    $subQ->where('full_name', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('school_number', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhere('field_name', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($filters['academic_year_id']) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if ($filters['class_id']) {
            $query->where('class_id', $filters['class_id']);
        }

        if ($filters['school_id']) {
            $query->where('school_id', $filters['school_id']);
        }

        $errorsLog = $query->latest()->paginate(10);
        return $errorsLog;
    }

    public function getErrorById(string $id)
    { 
        $errorRecord = Error::with([
            'student', 'createdBy', 
            'academicYear', 'schoolClass', 'school'])->findOrFail($id);
            return $errorRecord;
    }

    public function deleteError(string $id)
    {
        $errorRecord = Error::findOrFail($id);
        $this->authorize('viewAny', $errorRecord);
        $errorRecord->delete();
        return $errorRecord;
    }
}
