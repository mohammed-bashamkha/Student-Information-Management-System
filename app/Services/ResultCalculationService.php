<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\StudentEnrollment;
use App\Models\FinalResult;
use App\Services\ActivityLogServices\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class ResultCalculationService
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }
    public function calculateFinalResult($studentId, $academicYearId, $userId = null)
    {
        $userId = $userId ?? Auth::id() ?? 1;

        $enrollment = StudentEnrollment::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->with(['schoolClass.subjects', 'student'])
            ->first();

        if (!$enrollment) return null;

        $student = $enrollment->student;
        $isMale = $student->gender === 'male';
        $requiredSubjectsCount = $enrollment->schoolClass->subjects->count();

        $recordedGrades = Grade::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        $recordedCount = $recordedGrades->count();
        $totalSum = 0;
        $average = 0;

        if ($recordedCount > 0 && $recordedCount < $requiredSubjectsCount) {
            $finalStatus = $isMale ? 'غائب' : 'غائبة';
            $totalSum = $recordedGrades->sum('total');
            $average = $requiredSubjectsCount > 0 ? ($totalSum / $requiredSubjectsCount) : 0;
            $notes = "لم يتم رصد جميع الدرجات ($recordedCount من أصل $requiredSubjectsCount).";
        } elseif ($recordedCount === $requiredSubjectsCount && $requiredSubjectsCount > 0) {
            $totalSum = $recordedGrades->sum('total');
            $average = $totalSum / $requiredSubjectsCount;
            $hasFailedSubject = $recordedGrades->where('total', '<', 50)->count() > 0;

            if ($hasFailedSubject || $average < 50) {
                $finalStatus = $isMale ? 'راسب' : 'راسبة';
            } else {
                $finalStatus = $isMale ? 'ناجح' : 'ناجحة';
            }
            $notes = 'تم الحساب تلقائياً عبر النظام.';
        } else {
            return null;
        }

        $result = FinalResult::updateOrCreate(
            ['student_id' => $studentId, 'academic_year_id' => $academicYearId],
            [
                'total_student_grades' => $totalSum,
                'average_grade'        => $average,
                'final_result'         => $finalStatus,
                'school_id'            => $enrollment->school_id,
                'class_id'             => $enrollment->class_id,
                'created_by'           => $userId,
                'notes'                => $notes
            ]
        );

        $this->activityLogService->logAction(
            'final_results',
            $result,
            'update',
            "تم احتساب/تحديث النتيجة النهائية للطالب: {$student->full_name}"
        );

        return $result;
    }
}
