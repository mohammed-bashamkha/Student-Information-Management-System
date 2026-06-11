<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\School;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['students' => [], 'schools' => []]);
        }

        // Search Students: by full_name or school_number
        $students = Student::where(function ($q) use ($query) {
            $q->where('full_name', 'LIKE', "%{$query}%")
              ->orWhere('school_number', 'LIKE', "%{$query}%");
        })
        ->with(['currentEnrollment.school', 'currentEnrollment.schoolClass'])
        ->limit(6)
        ->get()
        ->map(function ($student) {
            $enrollment = $student->currentEnrollment;
            return [
                'id'            => $student->id,
                'full_name'     => $student->full_name,
                'school_number' => $student->school_number,
                'gender'        => $student->gender,
                'school_name'   => $enrollment?->school?->name ?? null,
                'class_name'    => $enrollment?->schoolClass?->name ?? null,
            ];
        });

        // Search Schools: by name
        $schools = School::where('name', 'LIKE', "%{$query}%")
            ->limit(4)
            ->get()
            ->map(function ($school) {
                return [
                    'id'          => $school->id,
                    'name'        => $school->name,
                    'school_type' => $school->school_type,
                    'capacity'    => $school->capacity,
                ];
            });

        return response()->json([
            'students' => $students,
            'schools'  => $schools,
        ]);
    }
}
