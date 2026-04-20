@extends('layouts.main')

@section('title', 'Nouvel encaissement')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-active', 'Nouvel encaissement')

@section('content')

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Nouvel encaissement</h5>
                        <a href="{{ route('backoffice.encaissements.index') }}" class="btn btn-outline-secondary">
                            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form action="{{ route('backoffice.encaissements.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">

                            {{-- Centre --}}
                            <div class="col-md-6">
                                <label class="form-label">Centre <span class="text-danger">*</span></label>
                                <select name="site_id" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach($sites as $s)
                                        <option value="{{ $s->id }}" {{ old('site_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Nom de l'etudiant --}}
                            <div class="col-md-6">
                                <label class="form-label">Nom de l'etudiant <span class="text-danger">*</span></label>
                                <input type="text" name="student_name" class="form-control" value="{{ old('student_name') }}" required>
                            </div>

                            {{-- Nom du payeur --}}
                            <div class="col-md-6">
                                <label class="form-label">Nom du payeur</label>
                                <input type="text" name="payer_name" class="form-control" value="{{ old('payer_name') }}">
                            </div>

                            {{-- Montant --}}
                            <div class="col-md-6">
                                <label class="form-label">Montant (MAD) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" value="{{ old('amount') }}" step="0.01" min="0" required>
                            </div>

                            {{-- Mode de paiement --}}
                            <div class="col-md-6">
                                <label class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach(['especes' => 'Especes', 'tpe' => 'TPE', 'virement' => 'Virement bancaire', 'cheque' => 'Cheque'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('payment_method') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Type de frais --}}
                            <div class="col-md-6">
                                <label class="form-label">Type de frais <span class="text-danger">*</span></label>
                                <select name="fee_type" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach(['inscription_a1' => 'Inscription A1/A2/B1', 'inscription_b2' => 'Inscription B2', 'mensualite' => 'Mensualite', 'examen_osd' => 'Examen OSD', 'autre' => 'Autre'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('fee_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Mois concerne --}}
                            <div class="col-md-6">
                                <label class="form-label">Mois concerne</label>
                                <input type="date" name="fee_month" class="form-control" value="{{ old('fee_month') }}">
                            </div>

                            {{-- Description du frais --}}
                            <div class="col-md-6">
                                <label class="form-label">Description du frais</label>
                                <input type="text" name="fee_description" class="form-control" value="{{ old('fee_description') }}">
                            </div>

                            {{-- Nom du groupe --}}
                            <div class="col-md-6">
                                <label class="form-label">Nom du groupe</label>
                                <input type="text" name="group_name" class="form-control" value="{{ old('group_name') }}">
                            </div>

                            {{-- Annee scolaire --}}
                            <div class="col-md-6">
                                <label class="form-label">Annee scolaire</label>
                                <input type="text" name="school_year" class="form-control" value="{{ old('school_year') }}" placeholder="2025/2026">
                            </div>

                            {{-- Date d'encaissement --}}
                            <div class="col-md-6">
                                <label class="form-label">Date d'encaissement <span class="text-danger">*</span></label>
                                <input type="date" name="collected_at" class="form-control" value="{{ old('collected_at') }}" required>
                            </div>

                            {{-- Operateur --}}
                            <div class="col-md-6">
                                <label class="form-label">Operateur</label>
                                <input type="text" name="operator_name" class="form-control" value="{{ old('operator_name') }}">
                            </div>

                            {{-- Reference --}}
                            <div class="col-md-6">
                                <label class="form-label">Reference</label>
                                <input type="text" name="reference" class="form-control" value="{{ old('reference') }}">
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            </div>

                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-check me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
