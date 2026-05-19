<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
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
            'field_name' => $this->translateField($this->field_name),
            'old_value' => $this->formatValue($this->field_name, $this->old_value),
            'new_value' => $this->formatValue($this->field_name, $this->new_value),
            'reason' => $this->reason,
            'student' => $this->whenLoaded('student'),
            'created_by' => $this->whenLoaded('createdBy'),
            'academic_year' => $this->whenLoaded('academicYear'),
            'school_class' => $this->whenLoaded('schoolClass'),
            'school' => $this->whenLoaded('school'),
            'all_errors' => ErrorResource::collection($this->when(isset($this->all_errors), $this->all_errors)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    protected function translateField($field)
    {
        $map = [
            'full_name' => 'الاسم الكامل',
            'school_number' => 'الرقم المدرسي',
            'seat_number' => 'رقم الجلوس',
            'nationality' => 'الجنسية',
            'gender' => 'الجنس',
            'date_of_birth' => 'تاريخ الميلاد',
            'place_of_birth' => 'مكان الميلاد',
            'registration_date' => 'تاريخ التسجيل',
            'school_id' => 'المدرسة',
            'class_id' => 'الصف',
            'academic_year_id' => 'العام الدراسي',
            'national_id' => 'الرقم الوطني',
        ];

        return $map[$field] ?? $field;
    }

    protected function formatValue($field, $value)
    {
        if (empty($value)) return $value;

        if ($field === 'school_id') {
            return \App\Models\School::find($value)?->name ?? $value;
        }

        if ($field === 'class_id') {
            return \App\Models\SchoolClass::find($value)?->name ?? $value;
        }

        if ($field === 'academic_year_id') {
            return \App\Models\AcademicYear::find($value)?->year ?? $value;
        }

        if ($field === 'gender') {
            return $value === 'male' ? 'ذكر' : ($value === 'female' ? 'أنثى' : $value);
        }

        return $value;
    }
}
