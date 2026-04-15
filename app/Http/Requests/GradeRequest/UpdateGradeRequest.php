<?php

namespace App\Http\Requests\GradeRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:school_classes,id',
            'first_semester_total' => 'required|numeric|min:0|max:50',
            'second_semester_total' => 'required|numeric|min:0|max:50',
        ];
    }
}
