<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransfersAdmissionRequest extends FormRequest
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
            'type'             => 'required|in:transfer,admission',
            'student_id'       => 'required|exists:students,id',
            'from_school_id'   => 'required_if:type,transfer|exists:schools,id|nullable',
            'to_school_id'     => 'required|exists:schools,id|different:from_school_id',
            
            'academic_year_id' => 'required|exists:academic_years,id',
            'request_date'     => 'required|date',
            'reason'           => 'nullable|string|max:500',
        ];
    }
}
