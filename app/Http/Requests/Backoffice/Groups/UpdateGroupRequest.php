<?php

namespace App\Http\Requests\Backoffice\Groups;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // If upcoming => no suivi dates
        if ($this->input('status') === 'upcoming') {
            $this->merge([
                'date_debut' => null,
                'date_fin'   => null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'site_id'       => ['required', 'exists:sites,id'],

            // ✅ teacher optional
            'teacher_id'    => ['nullable', 'exists:teachers,id'],

            'level'         => ['required', Rule::in(['A1', 'A2', 'B1', 'B2'])],

            'name'          => ['required', 'string', 'max:255'],
            'name_fr'       => ['nullable', 'string', 'max:255'],
            'name_en'       => ['nullable', 'string', 'max:255'],

            // period_label auto generated
            'time_range'    => ['required', 'string', 'max:255'],

            'status'        => ['required', Rule::in(['active', 'upcoming'])],

            // ✅ dates required only if active
            'date_debut'    => ['nullable', 'date', 'required_if:status,active'],
            'date_fin'      => ['nullable', 'date', 'required_if:status,active', 'after_or_equal:date_debut',
                function ($attribute, $value, $fail) {
                    if (!$value || !$this->input('date_debut') || $this->input('status') !== 'active') {
                        return;
                    }
                    $start = Carbon::parse($this->input('date_debut'));
                    $end = Carbon::parse($value);
                    $days = $start->diffInDays($end);
                    // 10 months = ~304 days, allow ±5 days tolerance for month length variations
                    if ($days < 299 || $days > 309) {
                        $months = round($days / 30.44, 1);
                        $fail("La duree du groupe doit etre environ 10 mois (actuellement ~{$months} mois).");
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'site_id.required'        => 'Veuillez sélectionner un centre GLS.',
            'teacher_id.exists'       => 'Enseignant invalide.',
            'level.required'          => 'Veuillez choisir un niveau.',

            'name.required'           => 'Le nom du groupe est obligatoire.',
            'time_range.required'     => 'L’horaire du groupe est obligatoire.',

            // Suivi du groupe (only when active)
            'date_debut.required_if'  => 'La date de début est obligatoire (groupe actif).',
            'date_fin.required_if'    => 'La date de fin est obligatoire (groupe actif).',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ];
    }
}
