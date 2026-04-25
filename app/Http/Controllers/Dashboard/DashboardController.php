<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CertificateReplacement;
use App\Models\School;
use App\Models\Student;
use App\Models\TransfersAdmission;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $studentsCount = Student::count();
        $schoolsCount = School::count();
        $privateSchoolsCount = School::where('school_type', 'private')->count();
        $publicSchoolsCount = School::where('school_type', 'public')->count();
        $transferAdmissionPending = TransfersAdmission::where('status', 'pending')->count();
        $transferPending = TransfersAdmission::where('status', 'pending')
            ->where('type', 'transfer')->count();
        $admissionPending = TransfersAdmission::where('status', 'pending')
            ->where('type', 'admission')->count();
        $certificateReplacements = CertificateReplacement::count();

        $expiredAdmissions = Student::whereHas('currentEnrollment', function ($q) {
            $q->where('status', 'suspended');
        })->count();

        $studentDensityAlerts = School::withCount('enrollments')
            ->where('capacity', '>', 0)
            ->get()
            ->map(function ($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'density_percentage' => round(($school->enrollments_count / $school->capacity) * 100)
                ];
            })
            ->sortByDesc('density_percentage')
            ->take(5)
            ->values()
            ->toArray();

        $recentActivities = Activity::with('causer')->latest()->take(5)->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'description' => $log->description,
                'user' => $log->causer ? $log->causer->name : 'النظام',
                'time' => $log->created_at->format('Y-m-d h:i A'),
                'type' => $log->event ?? $log->log_name,
            ];
        });

        return response()->json([
            'kpis' => [
                'total_students' => $studentsCount,
                'active_schools' => $schoolsCount,
                'schools_breakdown' => [
                    'government' => $publicSchoolsCount,
                    'private' => $privateSchoolsCount,
                ],
                'pending_transfers' => $transferPending,
                'certificate_replacements' => $certificateReplacements,
            ],
            'needs_attention' => [
                'transfers_awaiting_review' => $transferPending,
                'expired_temporary_admissions' => $expiredAdmissions,
                'incomplete_files' => 7, // TODO: Replace with dynamic query when file logic is added
            ],
            'student_density_alerts' => $studentDensityAlerts,
            'activity_log' => $recentActivities,
            'navigation_badges' => [
                'student_transfers' => $transferPending,
                'temporary_admissions' => $admissionPending,
            ]
        ], 200);
    }
}
