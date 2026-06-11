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
        $suspendedCount = 0;

        Log::info('[SuspendExpiredAdmissions] بدء عملية فحص وإيقاف القبولات المؤقتة المنتهية.');

        // جلب التسجيلات "النشطة" للطلاب الذين لديهم قبول مؤقت منتهي الصلاحية في نفس العام الدراسي
        StudentEnrollment::query()
            ->where('status', 'active')
            ->whereHas('student.transfers', function ($query) use ($today) {
                $query->where('type', 'admission')
                    ->where('status', 'approved')
                    ->whereNotNull('end_date')
                    ->where('end_date', '<', $today)
                    // ربط العام الدراسي للقبول بالعام الدراسي للتسجيل لضمان الدقة
                    ->whereColumn('transfers_admissions.academic_year_id', 'student_enrollments.academic_year_id');
            })
            ->chunkById(100, function ($enrollments) use (&$suspendedCount) {
                DB::transaction(function () use ($enrollments, &$suspendedCount) {
                    foreach ($enrollments as $enrollment) {
                        $enrollment->status = 'suspended';
                        $enrollment->save();
                        
                        $suspendedCount++;
                        Log::info("[SuspendExpiredAdmissions] تم تعليق الطالب ID={$enrollment->student_id} لتسجيله في العام الدراسي {$enrollment->academic_year_id} بسبب انتهاء قبوله المؤقت.");
                    }
                });
            });

        Log::info("[SuspendExpiredAdmissions] اكتمل. تم تعليق {$suspendedCount} طالب بنجاح.");
    }
}
