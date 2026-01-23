<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\FinalResultExport;
use App\Imports\FinalResultImport;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
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
    public function showImport()
    {
         // التعديل هنا: استخدام 'year' بدلاً من 'name' للترتيب
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();

        return view('import', ['academicYears' => $academicYears]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
        ]);

        try {
            Excel::import(new FinalResultImport($request->academic_year_id), $request->file('file'));
        } catch (\Exception $e) {

            return back()->withErrors(['msg' => 'حدث خطأ غير متوقع. الرجاء التأكد من أن أسماء الأعمدة والصفوف في الملف صحيحة ومطابقة للبيانات في النظام.','Erorr'=> $e->getMessage()]);
        }

        return back()->with('success', 'تم استيراد الملف بنجاح!');
    }
}
