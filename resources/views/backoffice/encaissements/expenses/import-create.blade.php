@extends('layouts.main')

@section('title', 'Importer des dépenses')
@section('breadcrumb-item', 'Charges')
@section('breadcrumb-item-active', 'Import')

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Importer des dépenses</h5>
                        <a href="{{ route('backoffice.encaissements.expenses.index') }}" class="btn btn-outline-secondary btn-sm">
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

                    <form action="{{ route('backoffice.encaissements.expenses.imports.store') }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Centre GLS <span class="text-danger">*</span></label>
                                <select name="site_id" class="form-select" required>
                                    <option value="">Sélectionner un centre</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Mois <span class="text-danger">*</span></label>
                                <input type="month" name="month" class="form-control"
                                       value="{{ old('month') }}" min="2023-01" max="{{ date('Y-m') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fichier (PDF ou Excel) <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control"
                                       accept=".xlsx,.xls,.csv,.pdf" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('backoffice.encaissements.expenses.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-upload me-1"></i> Importer
                            </button>
                        </div>
                    </form>

                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="ph-duotone ph-info me-1"></i>
                            Import depuis "Liste des dépenses" (PDF ou Excel). Formats supportés : .pdf, .xlsx, .xls, .csv
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
