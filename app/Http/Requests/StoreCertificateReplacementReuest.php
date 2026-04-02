<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCertificateReplacementReuest extends FormRequest
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
            'student_id'       => 'required|exists:students,id',
            'school_id'        => 'required|exists:schools,id',
            'class_id'        => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'certificate_type' => 'required|string|max:100',
            'notes'            => 'nullable|string|max:500',
            'request_date'     => 'required|string|date',
        ];
    }
}
