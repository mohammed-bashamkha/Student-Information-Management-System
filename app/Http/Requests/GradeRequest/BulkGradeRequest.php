<?php

namespace App\Http\Requests\GradeRequest;

use Illuminate\Foundation\Http\FormRequest;

class BulkGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'grades' => 'required|array',
            'grades.*.subject_id' => 'required|exists:subjects,id',
            'grades.*.first_semester' => 'nullable|numeric|min:0|max:50',
            'grades.*.second_semester' => 'nullable|numeric|min:0|max:50',
        ];
    }
}
