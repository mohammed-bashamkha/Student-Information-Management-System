<?php

namespace App\Http\Requests\TransfersAdmissionRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransfersAdmissionRequest extends FormRequest
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
            'status'        => 'required|in:pending,approved,rejected',
            'approval_date' => 'required_if:status,approved|date|nullable',
            'reason'        => 'nullable|string',
            'based_on'      => 'nullable|string'
        ];
    }
}
