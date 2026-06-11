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

        // Calculate previous year percentage
        $previousYearPercentage = 0;
        $previousYear = \App\Models\AcademicYear::where('id', '<', $academicYearId)->orderBy('id', 'desc')->first();
        if ($previousYear) {
            $prevQuery = Student::query();
            $prevQuery->whereHas('enrollments', function ($q) use ($previousYear, $filters) {
                $q->where('academic_year_id', $previousYear->id);
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
            if (!empty($filters['gender'])) {
                $prevQuery->where('gender', $filters['gender']);
            }
            $prevTotal = $prevQuery->count();

            if ($prevTotal > 0) {
                $previousYearPercentage = round((($total - $prevTotal) / $prevTotal) * 100, 1);
            } else if ($total > 0) {
                $previousYearPercentage = 100;
            }
        }

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
                'previousYearPercentage' => $previousYearPercentage,
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
        $filters['academic_year_id'] = $academicYearId;

        $finalResultService = app(\App\Services\FinalResultServices\FinalResultService::class);
        $resultsPaginator = $finalResultService->getFinalResults($filters, 15);

        $stats = [
            'passRate' => 0,
            'average' => 0,
            'topCount' => 0,
            'failRate' => 0,
            'previousYearPassRate' => 0,
            'highestStudent' => null,
        ];

        try {
            // Build a base DB query (not Eloquent) to avoid strict mode issues with GROUP BY
            $baseWhere = function ($q) use ($academicYearId, $filters) {
                if ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                }
                if (!empty($filters['school_id'])) {
                    $q->where('school_id', $filters['school_id']);
                }
                if (!empty($filters['class_id'])) {
                    $q->where('class_id', $filters['class_id']);
                }
            };

            $total = \Illuminate\Support\Facades\DB::table('final_results')->where($baseWhere)->count();

            if ($total > 0) {
                // Pass rate
                $passed = \Illuminate\Support\Facades\DB::table('final_results')
                    ->where($baseWhere)
                    ->where(function ($q) {
                        $q->where('final_result', 'LIKE', '%ناجح%')
                          ->orWhere('final_result', 'LIKE', '%ناجحة%');
                    })->count();

                $stats['passRate'] = round(($passed / $total) * 100);
                $stats['failRate'] = 100 - $stats['passRate'];

                // Average grade
                $average = \Illuminate\Support\Facades\DB::table('final_results')
                    ->where($baseWhere)
                    ->avg('average_grade');
                $stats['average'] = $average ? round((float) $average, 1) : 0;

                // Top 3 students per school
                $schoolCounts = \Illuminate\Support\Facades\DB::table('final_results')
                    ->select('school_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_students'))
                    ->where($baseWhere)
                    ->whereNotNull('school_id')
                    ->groupBy('school_id')
                    ->get();

                $topStudentsCount = 0;
                foreach ($schoolCounts as $row) {
                    $topStudentsCount += min($row->total_students, 3);
                }
                $stats['topCount'] = $topStudentsCount;

                // Highest student
                $highestResult = FinalResult::with('student')
                    ->where(function ($q) use ($academicYearId, $filters) {
                        if ($academicYearId) {
                            $q->where('academic_year_id', $academicYearId);
                        }
                        if (!empty($filters['school_id'])) {
                            $q->where('school_id', $filters['school_id']);
                        }
                        if (!empty($filters['class_id'])) {
                            $q->where('class_id', $filters['class_id']);
                        }
                    })
                    ->whereNotNull('average_grade')
                    ->orderByDesc('average_grade')
                    ->first();

                if ($highestResult && $highestResult->student) {
                    $stats['highestStudent'] = [
                        'name' => $highestResult->student->full_name ?? 'غير معروف',
                        'average' => $highestResult->average_grade,
                    ];
                }
            }

            // Previous year pass rate
            if ($academicYearId) {
                $previousYear = \App\Models\AcademicYear::where('id', '<', $academicYearId)->orderBy('id', 'desc')->first();
                if ($previousYear) {
                    $prevTotal = \Illuminate\Support\Facades\DB::table('final_results')
                        ->where('academic_year_id', $previousYear->id)
                        ->when(!empty($filters['school_id']), fn($q) => $q->where('school_id', $filters['school_id']))
                        ->when(!empty($filters['class_id']), fn($q) => $q->where('class_id', $filters['class_id']))
                        ->count();

                    if ($prevTotal > 0) {
                        $prevPassed = \Illuminate\Support\Facades\DB::table('final_results')
                            ->where('academic_year_id', $previousYear->id)
                            ->when(!empty($filters['school_id']), fn($q) => $q->where('school_id', $filters['school_id']))
                            ->when(!empty($filters['class_id']), fn($q) => $q->where('class_id', $filters['class_id']))
                            ->where(function ($q) {
                                $q->where('final_result', 'LIKE', '%ناجح%')
                                  ->orWhere('final_result', 'LIKE', '%ناجحة%');
                            })->count();
                        $stats['previousYearPassRate'] = round(($prevPassed / $prevTotal) * 100);
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Results report stats error: ' . $e->getMessage());
        }

        return [
            'stats' => $stats,
            'results' => $resultsPaginator,
        ];
    }
}
