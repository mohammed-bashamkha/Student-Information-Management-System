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

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('student', function ($subQ) use ($searchTerm) {
                    $subQ->where('full_name', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('school_number', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhere('field_name', 'LIKE', "%{$searchTerm}%");
            });
        }

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        $errorsLog = $query->select('student_id', 'academic_year_id', 'school_id', 'class_id', \DB::raw('MAX(id) as id'))
            ->groupBy('student_id', 'academic_year_id', 'school_id', 'class_id')
            ->latest('id')
            ->paginate(10);
            
        return $errorsLog;
    }

    public function getErrorById(string $id)
    { 
        $baseError = Error::with(['student', 'academicYear', 'schoolClass', 'school'])->findOrFail($id);
        
        $this->authorize('view', $baseError);

        $allErrors = Error::with([
            'student', 'createdBy', 
            'academicYear', 'schoolClass', 'school'])
            ->where('student_id', $baseError->student_id)
            ->where('academic_year_id', $baseError->academic_year_id)
            ->where('school_id', $baseError->school_id)
            ->where('class_id', $baseError->class_id)
            ->get();
            
        $baseError->all_errors = $allErrors;
        return $baseError;
    }

    public function deleteError(string $id)
    {
        $errorRecord = Error::with('student')->findOrFail($id);
        $this->authorize('delete', $errorRecord);
        $errorRecord->delete();
        return $errorRecord;
    }
}
