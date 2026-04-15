<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeRequest\StoreGradeRequest;
use App\Http\Requests\GradeRequest\UpdateGradeRequest;
use App\Models\FinalResult;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Services\ResultCalculationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    use AuthorizesRequests;
    protected $resultCalculationService;
    public function __construct(ResultCalculationService $resultCalculationService)
    {
        $this->resultCalculationService = $resultCalculationService;
    }
    public function index()
    {
        $this->authorize('viewAny', Grade::class);
        $grades = Grade::with(['student', 'subject', 'academicYear'])->paginate(5);
        return response()->json($grades);
    }

    public function store(StoreGradeRequest $request)
    {
        $this->authorize('create', Grade::class);
        $data = $request->validated();
        $subject = Subject::find($request->subject_id);

        if (!$subject) {
            return response()->json(['message' => 'المقرر الدراسي غير موجود'], 422);
        }

        $enrollment = StudentEnrollment::where('student_id', $request->student_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->with('schoolClass.subjects')
            ->first();

        if (!$enrollment || !$enrollment->schoolClass->subjects->contains($subject)) {
            return response()->json([
                'message' => 'الطالب غير مسجل في هذا المقرر لهذا العام الدراسي'
            ], 422);
        }
        $totalSemesteres = $request->first_semester_total + $request->second_semester_total;
        $data['total']      = $totalSemesteres;
        $data['created_by'] = Auth::id();
        $data['school_id']  = $enrollment->school_id;
        $data['class_id']   = $enrollment->class_id;
        $data['academic_year_id'] = $enrollment->academic_year_id;

        $grade = DB::transaction(function () use ($data) {
            $grade = Grade::create($data);

            $this->resultCalculationService->calculateFinalResult(
                $data['student_id'],
                $data['academic_year_id']
            );

            return $grade;
        });

        return response()->json([
            'message' => 'تم اضافة الدرجة بنجاح',
            'data' => $grade
        ], 201);
    }

    public function show(string $id)
    {
        $grade = Grade::with(['student', 'subject', 'academicYear'])->findOrFail($id);
        $this->authorize('view', $grade);
        return response()->json($grade);
    }

    public function update(UpdateGradeRequest $request, string $id)
    {
        $grade = Grade::findOrFail($id);
        $this->authorize('update', $grade);

        $data = $request->validated();
        $subject = Subject::find($request->subject_id);

        if (!$subject) {
            return response()->json(['message' => 'المقرر الدراسي غير موجود'], 422);
        }

        $enrollment = StudentEnrollment::where('student_id', $request->student_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->with('schoolClass.subjects')  // تحميل subjects داخل schoolClass
            ->first();

        if (!$enrollment || !$enrollment->schoolClass->subjects->contains($subject)) {
            return response()->json([
                'message' => "الطالب غير مسجل في مقرر ({$subject->name}) لهذا العام الدراسي"
            ], 422);
        }

        if (
            $grade->student_id != $request->student_id ||
            $grade->subject_id != $request->subject_id ||
            $grade->academic_year_id != $request->academic_year_id
        ) {
            return response()->json([
                'message' => 'لا يمكن تغيير الطالب أو المقرر أو العام الدراسي لهذه الدرجة'
            ], 422);
        }

        $totalSemesteres = $request->first_semester_total + $request->second_semester_total;
        $data['total'] = $totalSemesteres;

        DB::transaction(function () use ($grade, $data) {
            $grade->update($data);
            $this->resultCalculationService->calculateFinalResult(
                $grade->student_id,
                $grade->academic_year_id
            );
        });

        return response()->json([
            'message' => 'تم تحديث الدرجة بنجاح',
            'data' => $grade
        ]);
    }

    public function destroy(string $id)
    {
        $grade = Grade::findOrFail($id);
        $this->authorize('delete', $grade);

        $studentId      = $grade->student_id;
        $academicYearId = $grade->academic_year_id;

        DB::transaction(function () use ($grade, $studentId, $academicYearId) {
            $grade->delete();
            $this->resultCalculationService->calculateFinalResult($studentId, $academicYearId);
        });

        return response()->json([
            'message' => 'تم حذف الدرجة بنجاح',
            'ID'      => $grade->id
        ]);
    }
}
