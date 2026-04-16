@extends('layouts.main')

@section('title', 'Importer fichier CRM')
@section('breadcrumb-item', 'Suivi Paiement')
@section('breadcrumb-item-link', route('backoffice.payroll.dashboard'))
@section('breadcrumb-item-active', 'Nouvel import')

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5>Importer un fichier CRM Excel</h5>
                    <p class="text-muted mb-0">
                        Sélectionnez le groupe, le mois de début, et téléchargez le fichier Excel du CRM.
                    </p>
                </div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('backoffice.payroll.import.store') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            {{-- Group Selection --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Groupe <span class="text-danger">*</span></label>
                                <select name="group_id" class="form-select" required id="group-select">
                                    <option value="">Sélectionner un groupe</option>
                                    @foreach($groups as $group)
                                        @php
                                            $lastImport = $group->latestImport;
                                            $lastRate = $lastImport?->payment_per_student;
                                            $lastStart = $lastImport?->start_month?->format('Y-m');
                                            $teacherRate = $group->teacher?->payment_per_student;
                                        @endphp
                                        <option value="{{ $group->id }}"
                                                data-teacher="{{ $group->teacher?->name ?? '—' }}"
                                                data-level="{{ $group->level }}"
                                                data-rate="{{ $teacherRate ?? '' }}"
                                                data-debut="{{ $group->date_debut ? \Carbon\Carbon::parse($group->date_debut)->format('Y-m') : '' }}"
                                                data-last-rate="{{ $lastRate ?? '' }}"
                                                data-last-start="{{ $lastStart ?? '' }}"
                                                data-has-import="{{ $lastImport ? '1' : '0' }}"
                                                {{ old('group_id', $selectedGroupId) == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }} — {{ $group->level }}
                                            ({{ $group->teacher?->name ?? 'Sans enseignant' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Start Month --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Mois de début du groupe <span class="text-danger">*</span></label>
                                <input type="month" name="start_month"
                                       class="form-control"
                                       value="{{ old('start_month') }}"
                                       required>
                                <small class="text-muted">Le mois à partir duquel le groupe a commencé (ex: 2026-03)</small>
                            </div>

                            {{-- Group Info (auto-populated) --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Enseignant</label>
                                <input type="text" class="form-control" id="teacher-display" readonly disabled>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Niveau</label>
                                <input type="text" class="form-control" id="level-display" readonly disabled>
                            </div>

                            {{-- Payment Per Student Override --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Taux paiement par étudiant (DH)</label>
                                <input type="number" name="payment_per_student"
                                       class="form-control"
                                       value="{{ old('payment_per_student') }}"
                                       step="0.01" min="0"
                                       id="rate-input"
                                       placeholder="Laisser vide = taux enseignant">
                                <small class="text-muted" id="rate-hint"></small>
                            </div>

                            {{-- Excel File --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fichier Excel CRM <span class="text-danger">*</span></label>
                                <input type="file" name="file"
                                       class="form-control"
                                       accept=".xlsx,.xls,.csv"
                                       required>
                                <small class="text-muted">Formats acceptés: .xlsx, .xls, .csv (max 10 Mo)</small>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Notes (optionnel)</label>
                                <textarea name="notes" class="form-control" rows="3"
                                          placeholder="Notes sur cet import...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('backoffice.payroll.dashboard') }}" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-upload-simple me-1"></i> Importer
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // Auto-populate group info when selection changes
        document.getElementById('group-select').addEventListener('change', function () {
            const option = this.options[this.selectedIndex];
            const rateInput = document.getElementById('rate-input');
            const startMonthInput = document.querySelector('input[name="start_month"]');

            document.getElementById('teacher-display').value = option.dataset.teacher || '—';
            document.getElementById('level-display').value = option.dataset.level || '—';

            const teacherRate = option.dataset.rate;
            const lastRate = option.dataset.lastRate;
            const lastStart = option.dataset.lastStart;
            const hasImport = option.dataset.hasImport === '1';
            const rateHint = document.getElementById('rate-hint');

            // Pre-fill rate + build hint
            let hints = [];
            if (hasImport && lastRate) {
                rateInput.value = lastRate;
                rateInput.placeholder = '';
                hints.push('Dernier import : <strong>' + lastRate + ' DH</strong>');
            } else if (teacherRate) {
                rateInput.value = teacherRate;
                rateInput.placeholder = '';
            } else {
                rateInput.value = '';
                rateInput.placeholder = 'Ex: 300 ou 500';
            }
            if (teacherRate) hints.push('Taux enseignant : <strong>' + teacherRate + ' DH</strong>');
            rateHint.innerHTML = hints.length ? hints.join(' | ') : '';

            // Pre-fill start_month: use last import's start if exists, otherwise group date_debut
            if (hasImport && lastStart) {
                startMonthInput.value = lastStart;
            } else {
                const debut = option.dataset.debut;
                if (debut) startMonthInput.value = debut;
            }
        });

        // Trigger on page load if a group is pre-selected
        document.getElementById('group-select').dispatchEvent(new Event('change'));
    </script>
@endsection
