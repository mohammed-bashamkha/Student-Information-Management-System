<?php

namespace App\Jobs;

use App\Models\StudentEnrollment;
use App\Models\TransfersAdmission;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job يعمل يومياً للبحث عن طلاب انتهت صلاحية قبولهم المؤقت
 * ويغير status في student_enrollments إلى 'suspended'
 */
class SuspendExpiredAdmissionsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    public function handle(): void
    {
        $today = Carbon::today();

        // جلب جميع طلبات القبول المؤقت المقبولة التي انتهت صلاحيتها
        $expiredAdmissions = TransfersAdmission::query()
            ->where('type', 'admission')
            ->where('status', 'approved')
            ->whereNotNull('end_date')
            ->where('end_date', '<', $today)
            ->with('student')
            ->get();

        if ($expiredAdmissions->isEmpty()) {
            Log::info('[SuspendExpiredAdmissions] لا يوجد قبول مؤقت منتهي الصلاحية اليوم.');
            return;
        }

        $suspendedCount = 0;

        DB::transaction(function () use ($expiredAdmissions, &$suspendedCount) {
            foreach ($expiredAdmissions as $admission) {
                // تحديث تسجيل الطالب للعام الدراسي نفسه إلى suspended
                $updated = StudentEnrollment::where('student_id', $admission->student_id)
                    ->where('academic_year_id', $admission->academic_year_id)
                    ->where('status', 'active')  // فقط النشطة - تجنب الكتابة فوق حالات أخرى
                    ->update(['status' => 'suspended']);

                if ($updated > 0) {
                    $suspendedCount++;
                    Log::info("[SuspendExpiredAdmissions] تم تعليق الطالب ID={$admission->student_id} بعد انتهاء قبوله المؤقت في {$admission->end_date}");
                }
            }
        });

        Log::info("[SuspendExpiredAdmissions] اكتمل. تم تعليق {$suspendedCount} طالب.");
    }
}
