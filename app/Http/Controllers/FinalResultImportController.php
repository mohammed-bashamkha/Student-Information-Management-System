<?php

namespace App\Http\Controllers;

use App\Imports\FinalResultImport;
use App\Imports\FinalResultImportImproved;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class FinalResultImportController extends Controller
{
    /**
     * عرض صفحة الاستيراد
     */
    public function showImportForm()
    {
        return view('import');
    }

    /**
     * استيراد النتائج النهائية - الطريقة الأساسية
     */
    public function importBasic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:10240',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:school_classes,id',
            'school_id' => 'required|exists:schools,id',
        ], [
            'file.required' => 'يرجى اختيار ملف Excel',
            'file.mimes' => 'يجب أن يكون الملف بصيغة Excel (xlsx, xls)',
            'file.max' => 'حجم الملف يجب أن لا يتجاوز 10 ميجابايت',
            'academic_year_id.required' => 'يرجى اختيار السنة الدراسية',
            'class_id.required' => 'يرجى اختيار الصف',
            'school_id.required' => 'يرجى اختيار المدرسة',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            Excel::import(
                new FinalResultImport(
                    $request->academic_year_id,
                    $request->class_id,
                    $request->school_id
                ),
                $request->file('file')
            );

            return redirect()->back()->with('success', 'تم استيراد النتائج النهائية بنجاح');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * استيراد النتائج النهائية - الطريقة المحسّنة مع التقارير
     */
    public function importImproved(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:10240',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:school_classes,id',
            'school_id' => 'required|exists:schools,id',
        ], [
            'file.required' => 'يرجى اختيار ملف Excel',
            'file.mimes' => 'يجب أن يكون الملف بصيغة Excel (xlsx, xls)',
            'file.max' => 'حجم الملف يجب أن لا يتجاوز 10 ميجابايت',
            'academic_year_id.required' => 'يرجى اختيار السنة الدراسية',
            'class_id.required' => 'يرجى اختيار الصف',
            'school_id.required' => 'يرجى اختيار المدرسة',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $import = new FinalResultImportImproved(
                $request->academic_year_id,
                $request->class_id,
                $request->school_id
            );

            Excel::import($import, $request->file('file'));

            // الحصول على تقرير الاستيراد
            $report = $import->getImportReport();

            // التحقق من وجود أخطاء
            if ($report['summary']['failed'] > 0) {
                return redirect()->back()
                    ->with('warning', 'تم الاستيراد مع بعض الأخطاء')
                    ->with('import_report', $report);
            }

            return redirect()->back()
                ->with('success', 'تم استيراد النتائج النهائية بنجاح')
                ->with('import_report', $report);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * تصدير تقرير الاستيراد إلى JSON
     */
    public function exportImportReport(Request $request)
    {
        if (!$request->session()->has('import_report')) {
            return response()->json(['error' => 'لا يوجد تقرير استيراد متاح'], 404);
        }

        $report = $request->session()->get('import_report');
        
        return response()->json($report, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="import-report-' . date('Y-m-d-H-i-s') . '.json"'
        ]);
    }
}
