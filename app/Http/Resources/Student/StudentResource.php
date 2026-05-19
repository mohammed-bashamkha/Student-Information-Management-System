<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_number' => $this->school_number,
            'seat_number' => $this->seat_number,
            'full_name' => $this->full_name,
            'nationality' => $this->nationality,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'place_of_birth' => $this->place_of_birth,
            'registration_date' => $this->registration_date,
            'enrollments' => StudentEnrollmentResource::collection($this->whenLoaded('enrollments')),
            'current_enrollment' => new StudentEnrollmentResource($this->whenLoaded('currentEnrollment')),
        ];
    }
}
