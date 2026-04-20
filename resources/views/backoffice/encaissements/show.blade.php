@extends('layouts.main')

@section('title', 'Détail Encaissement')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-active', 'Détail #' . $encaissement->id)

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Encaissement #{{ $encaissement->id }}</h5>
                        <a href="{{ route('backoffice.encaissements.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Étudiant</label>
                            <p class="fw-semibold mb-0">{{ $encaissement->student_name }}</p>
                        </div>
                        @if($encaissement->payer_name)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Payeur</label>
                            <p class="mb-0">{{ $encaissement->payer_name }}</p>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label text-muted">Montant</label>
                            <p class="fw-bold text-primary mb-0">{{ number_format($encaissement->amount, 2, ',', ' ') }} DH</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Méthode de paiement</label>
                            <p class="mb-0"><span class="badge bg-light-success">{{ $encaissement->getPaymentMethodLabel() }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Type de frais</label>
                            <p class="mb-0">{{ $encaissement->getFeeTypeLabel() }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Date encaissement</label>
                            <p class="mb-0">{{ $encaissement->collected_at->format('d/m/Y') }}</p>
                        </div>
                        @if($encaissement->fee_month)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Mois concerné</label>
                            <p class="mb-0">{{ $encaissement->fee_month->translatedFormat('F Y') }}</p>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label text-muted">Centre</label>
                            <p class="mb-0">{{ $encaissement->site->name ?? '—' }}</p>
                        </div>
                        @if($encaissement->group_name)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Groupe</label>
                            <p class="mb-0">{{ $encaissement->group_name }}</p>
                        </div>
                        @endif
                        @if($encaissement->reference)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Référence</label>
                            <p class="mb-0">{{ $encaissement->reference }}</p>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label text-muted">Opérateur</label>
                            <p class="mb-0">{{ ucfirst($encaissement->operator_name ?? '—') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Source</label>
                            <p class="mb-0"><span class="badge bg-light-secondary">{{ $encaissement->source_system }}</span></p>
                        </div>
                        @if($encaissement->fee_description)
                        <div class="col-12">
                            <label class="form-label text-muted">Description frais (brut)</label>
                            <p class="mb-0">{{ $encaissement->fee_description }}</p>
                        </div>
                        @endif
                        @if($encaissement->notes)
                        <div class="col-12">
                            <label class="form-label text-muted">Notes</label>
                            <p class="mb-0">{{ $encaissement->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Actions</h6></div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('backoffice.encaissements.edit', $encaissement) }}" class="btn btn-warning">
                        <i class="ph-duotone ph-pencil-simple me-1"></i> Modifier
                    </a>
                    <form action="{{ route('backoffice.encaissements.destroy', $encaissement) }}" method="POST" onsubmit="return confirm('Supprimer cet encaissement ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger w-100">
                            <i class="ph-duotone ph-trash me-1"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
