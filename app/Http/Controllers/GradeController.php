<?php

namespace App\Http\Controllers;

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

    public function store(Request $request)
    {
        $this->authorize('create',Grade::class);
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'first_semester_total' => 'required|numeric|min:0|max:50',
            'second_semester_total' => 'required|numeric|min:0|max:50',
            'total' => 'required|numeric|min:0|max:100',
        ]);
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

    public function update(Request $request, string $id)
    {
        $grade = Grade::findOrFail($id);
        $this->authorize('update', $grade);

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'first_semester_total' => 'required|numeric|min:0|max:50',
            'second_semester_total' => 'required|numeric|min:0|max:50',
            'total' => 'required|numeric|min:0|max:100',
        ]);

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
        // 1. جلب بيانات تسجيل الطالب لمعرفة المواد المطلوبة منه في فصله الدراسي
        $enrollment = StudentEnrollment::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->with('schoolClass.subjects')
            ->first();

        if (!$enrollment) return;

        // 2. عدد المواد المطلوب دراستها لهذا الفصل
        $requiredSubjectsCount = $enrollment->schoolClass->subjects->count();

        // 3. جلب الدرجات التي تم رصدها فعلياً للطالب في هذه السنة
        $recordedGrades = Grade::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        // 4. الفحص: هل رصدنا درجات لجميع المواد؟
        if ($recordedGrades->count() === $requiredSubjectsCount && $requiredSubjectsCount > 0) {
            
            $totalSum = $recordedGrades->sum('total');
            $average = $totalSum / $requiredSubjectsCount;

            // تحديد الحالة بناءً على المعدل (يمكنك تعديل الشرط حسب قوانينكم)
            // مثال: إذا كان مجموع أي مادة أقل من 50 يعتبر "fail"
            $hasFailedSubject = $recordedGrades->where('total', '<', 50)->count() > 0;
            
            $checkGender = Student::find($studentId);
            if ($checkGender->gender == 'male') {
                $finalStatus = 'ناجح';
                if ($hasFailedSubject) {
                    $finalStatus = 'راسب';
                } elseif ($average < 50) {
                    $finalStatus = 'راسب';
                }
            }else {
                $finalStatus = 'ناجحة';
                if ($hasFailedSubject) {
                    $finalStatus = 'راسبة';
                } elseif ($average < 50) {
                    $finalStatus = 'راسبة';
                }
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
                    'created_by' => Auth::id(),
                    'notes' => 'تم الحساب تلقائياً بعد رصد آخر درجة.'
                ]
            );
        }
    }

}
