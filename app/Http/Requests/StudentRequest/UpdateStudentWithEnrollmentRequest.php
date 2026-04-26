<?php

namespace App\Http\Requests\StudentRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentWithEnrollmentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'nationality' => 'nullable|string',
        'gender' => 'required|string|in:male,female',
        'date_of_birth' => 'nullable|date',
        'registration_date' => 'nullable|date',
        'school_id' => 'required|exists:schools,id',
        'place_of_birth' => 'nullable|string',
        'class_id' => 'required|exists:school_classes,id',
        'academic_year_id' => 'required|exists:academic_years,id',
        'reason' => 'nullable|string|max:255',
        ];
    }
}
