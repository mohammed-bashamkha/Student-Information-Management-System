<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TransfersAdmission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * كنترولر إدارة الطلاب الموقوفين (انتهاء القبول المؤقت)
 *
 * GET  /suspended-students          → قائمة الطلاب الموقوفين
 * POST /suspended-students/{id}/restore → استعادة الطالب لمدرسته الأصلية
 */
class SuspendedStudentController extends Controller
{
    use AuthorizesRequests;

    /**
     * عرض قائمة الطلاب الموقوفين مع تفاصيل القبول المؤقت المنتهي
     */
    public function index(Request $request)
    {
        $query = StudentEnrollment::with([
            'student',
            'school',
            'schoolClass',
            'academicYear',
        ])
            ->where('status', 'suspended');

        // فلترة بالعام الدراسي
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // فلترة بالمدرسة الحالية (مدرسة القبول المؤقت)
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // بحث بالاسم أو الرقم
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                    ->orWhere('school_number', 'LIKE', "%{$search}%");
            });
        }

        $suspendedEnrollments = $query->latest()->paginate(15);

        // إضافة تفاصيل القبول المؤقت المنتهي لكل طالب
        $suspendedEnrollments->getCollection()->transform(function ($enrollment) {
            $expiredAdmission = TransfersAdmission::where('student_id', $enrollment->student_id)
                ->where('type', 'admission')
                ->where('status', 'approved')
                ->where('academic_year_id', $enrollment->academic_year_id)
                ->whereNotNull('end_date')
                ->where('end_date', '<', now()->toDateString())
                ->with(['fromSchool', 'toSchool', 'schoolClass'])
                ->latest()
                ->first();

            $enrollment->expired_admission = $expiredAdmission;
            $enrollment->original_school   = $expiredAdmission?->fromSchool;

            return $enrollment;
        });

        return response()->json([
            'message' => 'قائمة الطلاب الموقوفين بسبب انتهاء القبول المؤقت',
            'data'    => $suspendedEnrollments,
        ], 200);
    }

    /**
     * استعادة طالب موقوف: إلغاء القبول المؤقت وإعادته لمدرسته الأصلية
     *
     * @param int $studentId
     */
    public function restore(Request $request, int $studentId)
    {
        $enrollment = StudentEnrollment::where('student_id', $studentId)
            ->where('status', 'suspended')
            ->latest('id')
            ->firstOrFail();

        // جلب القبول المؤقت المنتهي الخاص بهذا الطالب وعامه الدراسي
        $expiredAdmission = TransfersAdmission::where('student_id', $studentId)
            ->where('type', 'admission')
            ->where('status', 'approved')
            ->where('academic_year_id', $enrollment->academic_year_id)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->toDateString())
            ->latest()
            ->first();

        if (!$expiredAdmission) {
            return response()->json([
                'message' => 'لم يُعثر على قبول مؤقت منتهٍ لهذا الطالب.',
            ], 404);
        }

        // التأكد من وجود مدرسة الأصل
        if (!$expiredAdmission->from_school_id) {
            return response()->json([
                'message' => 'لا يمكن تحديد المدرسة الأصلية للطالب.',
            ], 422);
        }

        DB::transaction(function () use ($enrollment, $expiredAdmission) {
            // 1. إعادة تسجيل الطالب لمدرسته الأصلية وتفعيله
            $enrollment->update([
                'status'    => 'active',
                'school_id' => $expiredAdmission->from_school_id,
                'class_id'  => $expiredAdmission->class_id,  // نفس الصف الذي كان فيه
            ]);

            // 2. حذف القبول المؤقت المنتهي
            $expiredAdmission->delete();
        });

        $enrollment->load(['student', 'school', 'schoolClass', 'academicYear']);

        activity('students')
            ->causedBy(Auth::user())
            ->performedOn($enrollment->student)
            ->event('restore')
            ->log('تم استعادة الطالب الموقوف: ' . $enrollment->student->full_name . ' إلى مدرسته الأصلية');

        return response()->json([
            'message' => 'تم استعادة الطالب بنجاح وإعادته لمدرسته الأصلية.',
            'data'    => [
                'student'          => $enrollment->student,
                'restored_to'      => $enrollment->school,
                'enrollment_status' => $enrollment->status,
                'academic_year'    => $enrollment->academicYear,
            ],
        ], 200);
    }
}
