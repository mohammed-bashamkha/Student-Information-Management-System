<?php

namespace App\Services\FinalResultServices;

use App\Models\FinalResult;

class FinalResultService
{
    public function getFinalResults(array $filters, int $perPage = 10)
    {
        $query = FinalResult::with([
            'student',
            'school',
            'schoolClass',
            'academicYear',
        ]);

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (!empty($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->whereHas('student', function ($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('school_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        $results = $query->orderBy('id', 'desc')->paginate($perPage);

        $transformed = $results->getCollection()->transform(function ($r) {
            $status = 'failed';
            $finalResultText = mb_strtolower($r->final_result);
            if (str_contains($finalResultText, 'ناجح') || str_contains($finalResultText, 'ناجحة')) {
                $status = 'passed';
            } elseif (str_contains($finalResultText, 'مشروط')) {
                $status = 'conditional';
            }

            return [
                'id' => $r->id,
                'student_id' => $r->student_id,
                'academic_year_id' => $r->academic_year_id,
                'school_id' => $r->school_id,
                'class_id' => $r->class_id,
                'total_score' => $r->total_student_grades,
                'average' => $r->average_grade,
                'status' => $status,
                'raw_status' => $r->final_result,
                'notes' => $r->notes,
                'created_at' => $r->created_at,
                'student' => $r->student,
                'school' => $r->school,
                'school_class' => $r->schoolClass,
            ];
        });

        $results->setCollection($transformed);
        return $results;
    }
}
