<?php

namespace App\Http\Controllers;

use App\Models\Error;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Exports\StudentErrorExport;
use Maatwebsite\Excel\Facades\Excel;

class ErrorController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Error::class);

        $query = Error::with([
            'student', 'createdBy', 
            'academicYear', 'schoolClass', 'school']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('student', function ($subQ) use ($searchTerm) {
                    $subQ->where('full_name', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('school_number', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhere('field_name', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $errorsLog = $query->latest()->paginate(10);

        return response()->json([
            'message' => 'تم جلب سجل التصحيحات بنجاح',
            'data' => $errorsLog
        ], 200);
    }

    public function show(string $id)
    {
        $errorRecord = Error::with([
            'student', 'createdBy', 
            'academicYear', 'schoolClass', 'school'])->findOrFail($id);

        return response()->json([
            'data' => $errorRecord
        ], 200);
    }
    public function destroy(string $id)
    {
        $errorRecord = Error::findOrFail($id);
        $this->authorize('viewAny', Error::class);
        $errorRecord->delete();

        return response()->json([
            'message' => 'تم حدف سجل خطاء بيانات الطالب بنجاح',
            'data' => $errorRecord->student()->full_name
        ], 200);
    }

    public function exportStudentErrors(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $fileName = 'Student_Errors_' . now()->format('Y_m_d') . '.xlsx';

        return Excel::download(
            new StudentErrorExport(
                $request->academic_year_id
            ),
            $fileName
        );
    }
}
