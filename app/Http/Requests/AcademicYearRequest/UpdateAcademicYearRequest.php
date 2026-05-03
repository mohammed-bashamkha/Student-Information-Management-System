<?php

namespace App\Http\Requests\AcademicYearRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicYearRequest extends FormRequest
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
            'year' => "sometimes|string|max:9|unique:academic_years,year,".$this->id,
            'start_date' => "sometimes|string|date",
            'end_date' => "sometimes|string|date",
            'status' => "nullable|in:active,inactive"
        ];
    }
}
