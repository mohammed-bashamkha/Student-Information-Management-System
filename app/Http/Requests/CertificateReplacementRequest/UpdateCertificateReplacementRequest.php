<?php

namespace App\Http\Requests\CertificateReplacementRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificateReplacementRequest extends FormRequest
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
            'certificate_type' => 'required|string|max:100',
            'notes'            => 'nullable|string|max:500',
            'request_date'     => 'nullable|date'
        ];
    }
}
