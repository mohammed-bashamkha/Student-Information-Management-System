<?php

namespace App\Services\ReportsServices;

use App\Models\Student;
use App\Models\School;
use App\Models\TransfersAdmission;
use App\Models\FinalResult;

class ReportsService
{
    public function studentsReport(array $filters = [])
    {
        $activeYearId = \App\Models\AcademicYear::where('status', 'active')->value('id');
        $academicYearId = $filters['academic_year_id'] ?? $activeYearId;
        $filters['academic_year_id'] = $academicYearId; // Ensure studentService uses it

        // Get paginated students
        $studentService = app(\App\Services\StudentServices\StudentService::class);
        $studentsPaginator = $studentService->getStudents($filters);
        $students = \App\Http\Resources\Student\StudentResource::collection($studentsPaginator)->response()->getData(true);

        // Build base query for stats
        $query = Student::query();
        
        if ($academicYearId || !empty($filters['school_id']) || !empty($filters['class_id']) || !empty($filters['status'])) {
            $query->whereHas('enrollments', function ($q) use ($academicYearId, $filters) {
                if ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                }
                if (!empty($filters['school_id'])) {
                    $q->where('school_id', $filters['school_id']);
                }
                if (!empty($filters['class_id'])) {
                    $q->where('class_id', $filters['class_id']);
                }
                if (!empty($filters['status'])) {
                    $q->where('status', $filters['status']);
                }
            });
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        $total = $query->count();
        $new = (clone $query)->whereYear('created_at', now()->year)->count();

        $transfersQuery = TransfersAdmission::where('type', 'transfer')->where('status', 'approved');
        if ($academicYearId) {
            $transfersQuery->where('academic_year_id', $academicYearId);
        }
        if (!empty($filters['school_id'])) {
            $transfersQuery->where(function($q) use ($filters) {
                $q->where('from_school_id', $filters['school_id'])
                  ->orWhere('to_school_id', $filters['school_id']);
            });
        }
        if (!empty($filters['class_id'])) {
            $transfersQuery->where('class_id', $filters['class_id']);
        }
        $villages = $transfersQuery->count();

        // Repeaters
        $repeatersQuery = FinalResult::where(function($q) {
            $q->where('final_result', 'LIKE', '%راسب%')
              ->orWhere('final_result', 'LIKE', '%راسبة%');
        });
        if ($academicYearId) {
            $repeatersQuery->where('academic_year_id', $academicYearId);
        }
        if (!empty($filters['school_id'])) {
            $repeatersQuery->where('school_id', $filters['school_id']);
        }
        if (!empty($filters['class_id'])) {
            $repeatersQuery->where('class_id', $filters['class_id']);
        }
        $repeaters = $repeatersQuery->count();

        return [
            'stats' => [
                'total' => $total,
                'new' => $new,
                'villages' => $villages,
                'repeaters' => $repeaters,
            ],
            'students' => $students
        ];
    }

    public function schoolsReport(array $filters = [])
    {
        $activeYearId = \App\Models\AcademicYear::where('status', 'active')->value('id');
        $academicYearId = $filters['academic_year_id'] ?? $activeYearId;

        $query = School::withCount(['enrollments' => function ($q) use ($academicYearId) {
            if ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            }
        }]);

        if (!empty($filters['search'])) {
            $query->where('name', 'LIKE', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['type'])) {
            $query->where('school_type', $filters['type']);
        }

        $schools = $query->get();
        
        $total = $schools->count();
        $overcrowded = $schools->filter(function ($school) {
            return $school->capacity > 0 && ($school->enrollments_count / $school->capacity) >= 0.9;
        })->count();
        $suspended = $schools->where('school_type', 'private')->count();
        $totalStudents = $schools->sum('enrollments_count');

        $schoolsData = $schools->map(function ($school) {
            $school->current_students = $school->enrollments_count;
            return $school;
        });

        return [
            'stats' => [
                'total' => $total,
                'overcrowded' => $overcrowded,
                'suspended' => $suspended,
                'totalStudents' => $totalStudents,
            ],
            'schools' => $schoolsData
        ];
    }

    public function transfersAdmissionsReport(array $filters = [])
    {
        $activeYearId = \App\Models\AcademicYear::where('status', 'active')->value('id');
        $academicYearId = $filters['academic_year_id'] ?? $activeYearId;
        
        $filters['type'] = 'transfer';
        $filters['academic_year_id'] = $academicYearId; // Ensure paginator uses it
        
        $transferAdmissionService = app(\App\Services\TransferAdmissionServices\TransferAdmissionService::class);
        $transfersPaginator = $transferAdmissionService->getTransfersAdmissions($filters);

        $query = TransfersAdmission::where('type', 'transfer');

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return [
            'stats' => [
                'total' => (clone $query)->count(),
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'approved' => (clone $query)->where('status', 'approved')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
            ],
            'transfers' => $transfersPaginator
        ];
    }

    public function finalResultsReport(array $filters = [])
    {
        $activeYearId = \App\Models\AcademicYear::where('status', 'active')->value('id');
        $academicYearId = $filters['academic_year_id'] ?? $activeYearId;
        $filters['academic_year_id'] = $academicYearId; // Ensure paginator uses it
        
        $finalResultService = app(\App\Services\FinalResultServices\FinalResultService::class);
        $resultsPaginator = $finalResultService->getFinalResults($filters, 15);

        $query = FinalResult::query();

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        if (!empty($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }
        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        $total = $query->count();
        
        $stats = [
            'passRate' => 0,
            'average' => 0,
            'topCount' => 0,
            'failRate' => 0,
            'previousYearPassRate' => 0,
        ];

        if ($total > 0) {
            $passed = (clone $query)->where(function($q) {
                $q->where('final_result', 'LIKE', '%ناجح%')
                  ->orWhere('final_result', 'LIKE', '%ناجحة%');
            })->count();

            $stats['passRate'] = round(($passed / $total) * 100);
            $stats['average'] = round((clone $query)->avg('average_grade'), 1);
            $stats['topCount'] = (clone $query)->where('average_grade', '>=', 95)->count();
            $stats['failRate'] = 100 - $stats['passRate'];
        }

        // Calculate previous year pass rate
        $previousYear = \App\Models\AcademicYear::where('id', '<', $academicYearId)->orderBy('id', 'desc')->first();
        if ($previousYear) {
            $prevQuery = FinalResult::where('academic_year_id', $previousYear->id);
            if (!empty($filters['school_id'])) {
                $prevQuery->where('school_id', $filters['school_id']);
            }
            if (!empty($filters['class_id'])) {
                $prevQuery->where('class_id', $filters['class_id']);
            }
            $prevTotal = $prevQuery->count();
            if ($prevTotal > 0) {
                $prevPassed = (clone $prevQuery)->where(function($q) {
                    $q->where('final_result', 'LIKE', '%ناجح%')
                      ->orWhere('final_result', 'LIKE', '%ناجحة%');
                })->count();
                $stats['previousYearPassRate'] = round(($prevPassed / $prevTotal) * 100);
            }
        }

        return [
            'stats' => $stats,
            'results' => $resultsPaginator
        ];
    }
}
