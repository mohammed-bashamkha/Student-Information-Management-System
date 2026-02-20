<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use App\Exports\FinalResultExport;
use App\Imports\FinalResultImport;
use App\Imports\FinalResultImportImproved;
use App\Models\AcademicYear;
use App\Models\FinalResult;
use App\Models\SchoolClass;
use App\Models\Subject;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class FinalResultController extends Controller
{
    public function index(Request $request)
    {
        // بناء الاستعلام الأساسي
        $query = FinalResult::with([
            'student.school',
            'student.schoolClass',
            'student.grades.subject'
        ]);

        // الفلترة حسب السنة الدراسية
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // الفلترة حسب الصف
        if ($request->filled('class_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // الفلترة حسب النتيجة النهائية
        if ($request->filled('final_result')) {
            $query->where('final_result', $request->final_result);
        }

        // البحث حسب اسم الطالب أو الرقم المدرسي
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('student', function($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('school_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        $finalResults = $query->orderBy('id', 'desc')->paginate(10);

        // جلب قائمة المواد
        $subjectsForHeader = [];
        if ($finalResults->isNotEmpty()) {
            $firstResultClassId = $finalResults->first()->student->schoolClass->id;
            $subjectsForHeader = SchoolClass::find($firstResultClassId)->subjects()->orderBy('id')->get();
        }

        // حساب الإحصائيات
        $stats = [
            'total' => $query->count(),
            'passed' => FinalResult::where('final_result', 'ناجح')->count(),
            'failed' => FinalResult::where('final_result', 'راسب')->count(),
        ];

        // بيانات للفلاتر
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $schoolClasses = SchoolClass::all();

        return view('final-result-index', [
            'finalResults' => $finalResults,
            'subjects' => $subjectsForHeader,
            'stats' => $stats,
            'academicYears' => $academicYears,
            'schoolClasses' => $schoolClasses,
        ]);
    }
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
        $school_classes = SchoolClass::where(['level_id'=> 1])->get();
        $schools = School::all();

        return view('import', [
            'academicYears' => $academicYears,
            'school_classes' => $school_classes,
            'schools' => $schools
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'class_id' => 'required|integer|exists:school_classes,id',
            'school_id' => 'required|integer|exists:schools,id',
        ], [
            'file.required' => 'يرجى اختيار ملف Excel',
            'file.mimes' => 'يجب أن يكون الملف بصيغة Excel (xlsx, xls)',
            'file.max' => 'حجم الملف يجب أن لا يتجاوز 10 ميجابايت',
        ]);

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
     * عرض تفاصيل نتيجة طالب معين
     */
    public function show($id)
    {
        $finalResult = FinalResult::with([
            'student.school',
            'student.schoolClass',
            'student.grades.subject',
            'academicYear'
        ])->findOrFail($id);

        $subjects = $finalResult->student->schoolClass->subjects()->orderBy('id')->get();

        return view('final-result-show', [
            'finalResult' => $finalResult,
            'subjects' => $subjects,
        ]);
    }
}
