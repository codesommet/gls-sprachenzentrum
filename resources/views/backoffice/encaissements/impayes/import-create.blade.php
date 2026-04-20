@extends('layouts.main')

@section('title', 'Importer impayés')
@section('breadcrumb-item', 'Recouvrement')
@section('breadcrumb-item-active', 'Import impayés')

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Importer liste d'impayés</h5>
                        <a href="{{ route('backoffice.encaissements.recouvrement') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    {{-- Info about cumulative exports --}}
                    <div class="alert alert-info mb-3">
                        <h6 class="mb-2"><i class="ph-duotone ph-info me-1"></i> Important : Export cumulatif du CRM</h6>
                        <p class="mb-1 small">Sur le CRM, vous générez le rapport en sélectionnant une <strong>date d'échéance</strong> : le fichier contient <strong>tous les impayés jusqu'à cette date</strong> (pas seulement un mois).</p>
                        <p class="mb-0 small">Lors d'un nouvel import, les étudiants qui ont payé depuis le dernier import sont automatiquement marqués comme <strong>recouvrés</strong>.</p>
                    </div>

                    <form action="{{ route('backoffice.encaissements.impayes.imports.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Centre GLS <span class="text-danger">*</span></label>
                                <select name="site_id" class="form-select" required>
                                    <option value="">Sélectionner un centre</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date d'échéance (arrêté du CRM) <span class="text-danger">*</span></label>
                                <input type="date" name="snapshot_date" class="form-control"
                                       value="{{ old('snapshot_date', now()->format('Y-m-d')) }}" required>
                                <small class="text-muted">Date utilisée sur le CRM pour générer le rapport</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Mois de référence <span class="text-danger">*</span></label>
                                <input type="month" name="month" class="form-control"
                                       value="{{ old('month', now()->format('Y-m')) }}" min="2023-01" max="{{ now()->addMonth()->format('Y-m') }}" required>
                                <small class="text-muted">Mois concerné par cet import</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fichier (Excel ou PDF) <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv,.pdf" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('backoffice.encaissements.recouvrement') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-upload me-1"></i> Importer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
