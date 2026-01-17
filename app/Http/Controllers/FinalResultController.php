<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\FinalResultExport;
use App\Models\AcademicYear;
use Maatwebsite\Excel\Facades\Excel;

class FinalResultController extends Controller
{
    public function export(Request $request)
    {
        try
        {
            $request->validate([
            'class_name' => 'required|string',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            ]);

            $className = $request->class_name;
            $academicYearId = $request->academic_year_id;

            $academicYear = AcademicYear::findOrFail($academicYearId);

            $fileName = 'final_result_' . $className . '_year_' . $academicYear->year . '.xlsx';

            return Excel::download(
                new FinalResultExport($className, $academicYearId),
                $fileName
            );
        }
        catch (\Exception $e)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export final results: ' . $e->getMessage(),
            ], 500);
        }
    }
}
