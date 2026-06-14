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
        $this->authorize('viewAllSuspendedStudents', Student::class);
        $query = StudentEnrollment::with([
            'student.transfers' => function ($q) {
                $q->where('type', 'admission')
                  ->where('status', 'approved')
                  ->whereNotNull('end_date')
                  ->where('end_date', '<', now()->toDateString())
                  ->with(['fromSchool', 'toSchool', 'schoolClass'])
                  ->latest();
            },
            'school',
            'schoolClass',
            'academicYear',
        ])
        ->where('status', 'suspended')
        ->whereHas('student.transfers', function ($q) {
            $q->where('type', 'admission')
              ->where('status', 'approved')
              ->whereNotNull('end_date')
              ->where('end_date', '<', now()->toDateString())
              ->whereColumn('transfers_admissions.academic_year_id', 'student_enrollments.academic_year_id');
        });

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

        // إضافة تفاصيل القبول المؤقت المنتهي لكل طالب بدون N+1 Queries
        $suspendedEnrollments->getCollection()->transform(function ($enrollment) {
            $expiredAdmission = $enrollment->student?->transfers
                ->where('academic_year_id', $enrollment->academic_year_id)
                ->first();

            if ($expiredAdmission) {
                $enrollment->setRelation('expired_admission', $expiredAdmission);
                if ($expiredAdmission->fromSchool) {
                    $enrollment->setRelation('original_school', $expiredAdmission->fromSchool);
                }
            }

            // إخفاء relation transfers حتى لا يرسل بيانات ضخمة، بدلاً من unset لتفادي تعطل الكود إذا تكرر الطالب
            if ($enrollment->student) {
                $enrollment->student->makeHidden(['transfers']);
            }

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
        $this->authorize('activateSuspendedStudent', Student::class);
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

        $action = $request->input('action', 'return_to_original');

        DB::transaction(function () use ($enrollment, $expiredAdmission, $action) {
            if ($action === 'permanent_transfer') {
                // تفعيل الطالب في المدرسة الحالية وتحديث نوع القبول
                $enrollment->update([
                    'status' => 'active',
                ]);
                
                $expiredAdmission->update([
                    'type' => 'transfer',
                    'end_date' => null,
                ]);
            } else {
                // 1. إعادة تسجيل الطالب لمدرسته الأصلية وتفعيله
                $enrollment->update([
                    'status'    => 'active',
                    'school_id' => $expiredAdmission->from_school_id,
                    'class_id'  => $expiredAdmission->class_id,  // نفس الصف الذي كان فيه
                ]);

                // 2. حذف القبول المؤقت المنتهي
                $expiredAdmission->delete();
            }
        });

        $enrollment->load(['student', 'school', 'schoolClass', 'academicYear']);

        if ($action === 'permanent_transfer') {
            activity('students')
                ->causedBy(Auth::user())
                ->performedOn($enrollment->student)
                ->event('restore')
                ->log('تم تفعيل الطالب الموقوف: ' . $enrollment->student->full_name . ' وتحويله بشكل دائم للمدرسة الحالية');
        } else {
            activity('students')
                ->causedBy(Auth::user())
                ->performedOn($enrollment->student)
                ->event('restore')
                ->log('تم استعادة الطالب الموقوف: ' . $enrollment->student->full_name . ' إلى مدرسته الأصلية');
        }

        return response()->json([
            'message' => $action === 'permanent_transfer' 
                ? 'تم تفعيل الطالب بنجاح وتحويله بشكل دائم للمدرسة الحالية.' 
                : 'تم استعادة الطالب بنجاح وإعادته لمدرسته الأصلية.',
            'data'    => [
                'student'          => $enrollment->student,
                'restored_to'      => $enrollment->school,
                'enrollment_status' => $enrollment->status,
                'academic_year'    => $enrollment->academicYear,
            ],
        ], 200);
    }
}
