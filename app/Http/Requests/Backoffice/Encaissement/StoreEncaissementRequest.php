<?php

namespace App\Http\Requests\Backoffice\Encaissement;

use Illuminate\Foundation\Http\FormRequest;

class StoreEncaissementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id'        => 'required|exists:sites,id',
            'student_name'   => 'required|string|max:255',
            'payer_name'     => 'nullable|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:especes,tpe,virement,cheque',
            'fee_type'       => 'required|in:inscription_a1,inscription_b2,mensualite,examen_osd,autre',
            'fee_month'      => 'nullable|date',
            'fee_description' => 'nullable|string|max:500',
            'group_name'     => 'nullable|string|max:255',
            'school_year'    => 'nullable|string|max:20',
            'collected_at'   => 'required|date',
            'operator_name'  => 'nullable|string|max:255',
            'reference'      => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'site_id.required' => 'Veuillez sélectionner un centre.',
            'student_name.required' => 'Le nom de l\'étudiant est obligatoire.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.min' => 'Le montant doit être supérieur à 0.',
            'collected_at.required' => 'La date d\'encaissement est obligatoire.',
        ];
    }
}
