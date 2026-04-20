<?php

namespace App\Http\Requests\Backoffice\Encaissement;

use Illuminate\Foundation\Http\FormRequest;

class StorePrimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'site_id'     => 'required|exists:sites,id',
            'amount'      => 'required|numeric|min:0.01',
            'month'       => 'required|date',
            'type'        => 'required|in:performance,collection,assiduite,autre',
            'reason'      => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Veuillez sélectionner un employé.',
            'site_id.required' => 'Veuillez sélectionner un centre.',
            'amount.required' => 'Le montant est obligatoire.',
            'month.required' => 'Le mois est obligatoire.',
        ];
    }
}
