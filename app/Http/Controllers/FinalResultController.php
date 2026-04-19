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
use App\Models\StudentEnrollment;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class FinalResultController extends Controller
{
    public function index(Request $request)
    {
        $query = FinalResult::with([
            'student.grades.subject',
            'student.currentEnrollment.schoolClass',
            'student.currentEnrollment.school',
            'school',
            'schoolClass',
            'academicYear',
        ]);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student.enrollments', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
                if ($request->filled('academic_year_id')) {
                    $q->where('academic_year_id', $request->academic_year_id);
                }
            });
        }

        if ($request->filled('final_result')) {
            $query->where('final_result', $request->final_result);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('student', function ($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('school_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        $finalResults = $query->orderBy('id', 'desc')->paginate(10);

        $subjectsForHeader = collect();
        if ($request->filled('class_id')) {
            $classForHeader = SchoolClass::find($request->class_id);
            if ($classForHeader) {
                $subjectsForHeader = $classForHeader->subjects()->orderBy('id')->get();
            }
        } elseif ($finalResults->isNotEmpty()) {
            $firstResult = $finalResults->first();
            // استخدم class_id مباشرةً من final_results
            $classId = $firstResult->class_id;
            if ($classId) {
                $classForHeader = SchoolClass::find($classId);
                if ($classForHeader) {
                    $subjectsForHeader = $classForHeader->subjects()->orderBy('id')->get();
                }
            }
        }

        $baseQuery = FinalResult::query();
        if ($request->filled('academic_year_id')) {
            $baseQuery->where('academic_year_id', $request->academic_year_id);
        }
        $stats = [
            'total'  => $baseQuery->clone()->count(),
            'passed' => $baseQuery->clone()->where('final_result', 'ناجح'),
            'failed' => $baseQuery->clone()->where('final_result', 'راسب')->count(),
        ];
        // بيانات للفلاتر
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $schoolClasses = SchoolClass::all();

        return view('final-result-index', [
            'finalResults'  => $finalResults,
            'subjects'      => $subjectsForHeader,
            'stats'         => $stats,
            'academicYears' => $academicYears,
            'schoolClasses' => $schoolClasses,
        ]);
    }
    public function showImport()
    {
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $school_classes = SchoolClass::all();
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

            if ($request->boolean('preview')) {
                $import->preview = true;
            }

            Excel::import($import, $request->file('file'));

            // الحصول على تقرير الاستيراد
            $report = $import->getImportReport();

            if ($request->boolean('preview')) {
                return response()->json([
                    'status' => 'preview',
                    'report' => $report,
                    'sample_data' => $import->previewData,
                ]);
            }

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

    public function show($id)
    {
        $finalResult = FinalResult::with([
            'student.grades.subject',
            'school',
            'schoolClass.subjects',
            'academicYear'
        ])->findOrFail($id);

        $subjects = $finalResult->schoolClass?->subjects()->orderBy('id')->get() ?? collect();

        return view('final-result-show', [
            'finalResult' => $finalResult,
            'subjects' => $subjects,
        ]);
    }
}
