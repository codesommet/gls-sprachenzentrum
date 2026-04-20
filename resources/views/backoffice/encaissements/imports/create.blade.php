@extends('layouts.main')

@section('title', 'Importer des encaissements')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-link', route('backoffice.encaissements.dashboard'))
@section('breadcrumb-item-active', 'Import')

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Importer des encaissements</h5>
                        <a href="{{ route('backoffice.encaissements.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                        </a>
                    </div>
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

                    <form action="{{ route('backoffice.encaissements.imports.store') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            {{-- Centre GLS --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Centre GLS <span class="text-danger">*</span></label>
                                <select name="site_id" class="form-select" required>
                                    <option value="">Selectionner un centre</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Format CRM --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Format CRM <span class="text-danger">*</span></label>
                                <select name="source_system" class="form-select" required>
                                    <option value="">Selectionner le format</option>
                                    <option value="old_crm" {{ old('source_system') === 'old_crm' ? 'selected' : '' }}>
                                        Nawaat (2023-2024)
                                    </option>
                                    <option value="new_crm" {{ old('source_system') === 'new_crm' ? 'selected' : '' }}>
                                        Wimsschool (2025+)
                                    </option>
                                </select>
                            </div>

                            {{-- Mois --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Mois <span class="text-danger">*</span></label>
                                <input type="month" name="month" id="monthPicker"
                                       class="form-control"
                                       value="{{ old('month') }}"
                                       min="2023-01" max="{{ date('Y-m') }}"
                                       required>
                            </div>

                            {{-- Annee scolaire (auto-filled) --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Annee scolaire <small class="text-muted">(auto)</small></label>
                                <input type="text" name="school_year" id="schoolYearInput"
                                       class="form-control"
                                       value="{{ old('school_year') }}"
                                       placeholder="2024/2025" readonly>
                            </div>

                            {{-- Fichier --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fichier (Excel ou PDF) <span class="text-danger">*</span></label>
                                <input type="file" name="file"
                                       class="form-control"
                                       accept=".xlsx,.xls,.csv,.pdf"
                                       required>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"
                                          placeholder="Notes sur cet import...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('backoffice.encaissements.dashboard') }}" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-upload me-1"></i> Importer
                            </button>
                        </div>
                    </form>

                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="ph-duotone ph-info me-1"></i>
                            Formats supportes : Excel (.xlsx, .xls, .csv) et PDF. Taille max : 20 Mo.
                        </small>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthPicker = document.getElementById('monthPicker');
    const schoolYearInput = document.getElementById('schoolYearInput');

    function updateSchoolYear() {
        const val = monthPicker.value;
        if (!val) { schoolYearInput.value = ''; return; }

        const [year, month] = val.split('-').map(Number);
        // School year: Sep-Dec = year/year+1, Jan-Aug = year-1/year
        if (month >= 9) {
            schoolYearInput.value = year + '/' + (year + 1);
        } else {
            schoolYearInput.value = (year - 1) + '/' + year;
        }
    }

    monthPicker.addEventListener('change', updateSchoolYear);
    if (monthPicker.value) updateSchoolYear();
});
</script>
@endsection
