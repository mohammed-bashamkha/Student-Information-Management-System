<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentEnrollmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'school_id'        => $this->school_id,
            'class_id'         => $this->class_id,
            'academic_year_id' => $this->academic_year_id,
            'status'           => $this->status,
            'school_name'      => $this->whenLoaded('school', fn() => $this->school->name),
            'class_name'       => $this->whenLoaded('schoolClass', fn() => $this->schoolClass->name),
            'academic_year'    => $this->whenLoaded('academicYear', fn() => $this->academicYear->year ?? $this->academicYear->name ?? null),
            'has_transfer'            => $this->whenLoaded('student', fn() =>
                $this->student->transfers()
                    ->where('academic_year_id', $this->academic_year_id)
                    ->where('type', 'transfer')
                    ->exists()
            ),
            'has_temporary_admission' => $this->whenLoaded('student', fn() =>
                $this->student->transfers()
                    ->where('academic_year_id', $this->academic_year_id)
                    ->where('type', 'admission')
                    ->exists()
            ),
            'has_data_errors'         => $this->whenLoaded('student', fn() =>
                $this->student->errors()->where('academic_year_id', $this->academic_year_id)->exists()
            ),
            'has_replacement'         => $this->whenLoaded('student', fn() =>
                $this->student->certificateReplacements()->where('academic_year_id', $this->academic_year_id)->exists()
            ),
            'has_final_result'        => \App\Models\FinalResult::where('student_id', $this->student_id)
                ->where('academic_year_id', $this->academic_year_id)->exists(),
        ];
    }
}
