<?php

namespace App\Http\Requests\Backoffice\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:active,cancelled,transferred',
        ];
    }
}
