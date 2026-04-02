<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use App\Models\FinalResult;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny', Grade::class);
        $grades = Grade::with(['student', 'subject', 'academicYear'])->paginate(5);
        return response()->json($grades);
    }

    public function store(StoreGradeRequest $request)
    {
        $this->authorize('create',Grade::class);
        $data = $request->validated();
        $subject = Subject::find($request->subject_id);
        $enrollment = StudentEnrollment::where('student_id', $request->student_id)
        ->where('academic_year_id', $request->academic_year_id)
        ->with('schoolClass')
        ->first();
        if (!$enrollment || !$enrollment->schoolClass->subjects->contains($subject)) {
            return response()->json([
                'message' => 'الطالب غير مسجل في هذا المقرر لهذا العام الدراسي'
            ], 422);
        }
        $totalSemesteres = $request->first_semester_total + $request->second_semester_total;
        $data['total'] = $totalSemesteres;
        $data['created_by'] = Auth::id();
            $grade = DB::transaction(function () use ($data) {
            $grade = Grade::create($data);

            // استدعاء دالة الفحص والحساب الآلي
            $this->checkAndCalculateFinalResult($data['student_id'], $data['academic_year_id']);

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
        $enrollment = StudentEnrollment::where('student_id', $request->student_id)
        ->where('academic_year_id', $request->academic_year_id)
        ->with('schoolClass')
        ->first();
        if (!$enrollment || !$enrollment->schoolClass->subjects->contains($subject)) {
            return response()->json([
                'message' => "الطالب غير مسجل في مقرر ($subject->name) لهذا العام الدراسي"
            ], 422);
        }
        if($grade->id !== $grade->student_id || $grade->id !== $grade->subject_id || $grade->id !== $grade->academic_year_id){
            return response()->json([
                'message' => 'لا يمكن تغيير الطالب أو المقرر أو العام الدراسي لهذه الدرجة'
            ], 422);
        }
        DB::transaction(function () use ($grade, $data) {
            $grade->update($data);
            $this->checkAndCalculateFinalResult($grade->student_id, $grade->academic_year_id);
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
        $grade->delete();
        return response()->json([
            'message' => 'تم حذف الدرجة بنجاح',
            'ID' => $grade->id
        ]);
    }

    protected function checkAndCalculateFinalResult($studentId, $academicYearId)
{
    // 1. جلب بيانات تسجيل الطالب ومعرفة الطالب نفسه للجنس
    $enrollment = StudentEnrollment::where('student_id', $studentId)
        ->where('academic_year_id', $academicYearId)
        ->with(['schoolClass.subjects', 'student'])
        ->first();

    if (!$enrollment) return;

    $student = $enrollment->student;
    $isMale = $student->gender === 'male';

    // 2. عدد المواد المطلوب دراستها لهذا الصف
    $requiredSubjectsCount = $enrollment->schoolClass->subjects->count();

    // 3. جلب الدرجات التي تم رصدها فعلياً
    $recordedGrades = Grade::where('student_id', $studentId)
        ->where('academic_year_id', $academicYearId)
        ->get();

    $recordedCount = $recordedGrades->count();

    // 4. تحديد الحالة النهائية (ناجح، راسب، غائب)
    if ($recordedCount > 0 && $recordedCount < $requiredSubjectsCount) {
        // حالة الغياب: إذا رصدنا بعض المواد ولم نكمل البقية
        $finalStatus = $isMale ? 'غائب' : 'غائبة';
        $totalSum = $recordedGrades->sum('total');
        $average = $requiredSubjectsCount > 0 ? ($totalSum / $requiredSubjectsCount) : 0;
        $notes = "لم يتم رصد جميع الدرجات ($recordedCount من أصل $requiredSubjectsCount).";
    } 
    elseif ($recordedCount === $requiredSubjectsCount && $requiredSubjectsCount > 0) {
        // حالة اكتمال الدرجات: فحص النجاح والرسوب
        $totalSum = $recordedGrades->sum('total');
        $average = $totalSum / $requiredSubjectsCount;
        
        // فحص إذا كان لديه مادة أقل من 50
        $hasFailedSubject = $recordedGrades->where('total', '<', 50)->count() > 0;

        if ($hasFailedSubject || $average < 50) {
            $finalStatus = $isMale ? 'راسب' : 'راسبة';
        } else {
            $finalStatus = $isMale ? 'ناجح' : 'ناجحة';
        }
        $notes = 'تم الحساب تلقائياً بعد اكتمال رصد الدرجات.';
    } 
    else {
        // إذا لم يتم رصد أي درجة إطلاقاً
        return; 
    }

    // 5. حفظ أو تحديث النتيجة النهائية
    FinalResult::updateOrCreate(
        [
            'student_id' => $studentId,
            'academic_year_id' => $academicYearId,
        ],
        [
            'total_student_grades' => $totalSum,
            'average_grade' => $average,
            'final_result' => $finalStatus,
            'created_by' => Auth::id() ?? $studentId, // في حال التشغيل من التعديل
            'notes' => $notes
        ]
    );
}


}
