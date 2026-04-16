<?php

namespace App\Http\Middleware;

use App\Models\StudentEnrollment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يتحقق أن الطالب المُشار إليه في الطلب ليس معلّقاً (suspended)
 * يجب استخدامه على أي Route يحتاج student_id في الـ request body
 *
 * الاستخدام:
 *   Route::post('/grades', ...)->middleware('student.not_suspended');
 */
class EnsureStudentNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $studentId = $request->input('student_id')
            ?? $request->route('student_id');

        if (!$studentId) {
            return $next($request); // لا يوجد student_id في الطلب — تجاوز الفحص
        }

        // البحث عن آخر تسجيل نشط للطالب
        $enrollment = StudentEnrollment::where('student_id', $studentId)
            ->latest('id')
            ->first();

        if ($enrollment && $enrollment->status === 'suspended') {
            return response()->json([
                'message'   => 'لا يمكن تنفيذ هذه العملية. الطالب موقوف حالياً بسبب انتهاء صلاحية قبوله المؤقت.',
                'student_id' => $studentId,
                'hint'      => 'يرجى مراجعة طلبات القبول المؤقت للطالب أو تحديث حالة تسجيله.',
            ], 403);
        }

        return $next($request);
    }
}
