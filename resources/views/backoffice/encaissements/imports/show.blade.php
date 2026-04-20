@extends('layouts.main')

@section('title', 'Detail import #' . $import->id)
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-link', route('backoffice.encaissements.dashboard'))
@section('breadcrumb-item-active', 'Import #' . $import->id)

@section('content')

    {{-- Toast --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Encaissements</strong>
                    <small>Maintenant</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Import Metadata --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-2">
                                Import #{{ $import->id }}
                                @switch($import->status)
                                    @case('completed')
                                        <span class="badge bg-light-success ms-2">Termine</span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-light-danger ms-2">Echoue</span>
                                        @break
                                    @case('processing')
                                        <span class="badge bg-light-warning ms-2">En cours</span>
                                        @break
                                    @default
                                        <span class="badge bg-light-secondary ms-2">En attente</span>
                                @endswitch
                            </h5>
                            <div class="row text-muted">
                                <div class="col-auto">
                                    <strong>Centre :</strong> {{ $import->site->name ?? '—' }}
                                </div>
                                <div class="col-auto">
                                    <strong>Format :</strong>
                                    {{ $import->source_system === 'old_crm' ? 'Nawat (2023 - Oct. 2025)' : 'Wimschool (Nov. 2025+)' }}
                                </div>
                                <div class="col-auto">
                                    <strong>Fichier :</strong> {{ $import->file_name }}
                                    <span class="badge bg-light-secondary ms-1">{{ strtoupper($import->file_type ?? 'excel') }}</span>
                                </div>
                            </div>
                            <div class="row text-muted mt-1">
                                <div class="col-auto">
                                    <strong>Date :</strong> {{ $import->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="col-auto">
                                    <strong>Importe par :</strong> {{ $import->importedBy->name ?? '—' }}
                                </div>
                                @if($import->month)
                                    <div class="col-auto">
                                        <strong>Mois :</strong>
                                        <span class="badge bg-primary">{{ \Carbon\Carbon::parse($import->month . '-01')->translatedFormat('F Y') }}</span>
                                    </div>
                                @endif
                                @if($import->school_year)
                                    <div class="col-auto">
                                        <strong>Annee scolaire :</strong> {{ $import->school_year }}
                                    </div>
                                @endif
                            </div>
                            @if($import->notes)
                                <div class="mt-2"><em>{{ $import->notes }}</em></div>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('backoffice.encaissements.imports.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                            </a>
                            <form action="{{ route('backoffice.encaissements.imports.destroy', $import) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer cet import et tous ses encaissements ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">
                                    <i class="ph-duotone ph-trash me-1"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row mb-3">
        <div class="col-md-2">
            <div class="card bg-light-primary">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1">Total lignes</h6>
                    <h4 class="text-primary mb-0">{{ number_format($import->total_rows ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-light-success">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1">Succes</h6>
                    <h4 class="text-success mb-0">{{ number_format($import->success_rows ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-light-danger">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1">Erreurs</h6>
                    <h4 class="text-danger mb-0">{{ number_format($import->error_rows ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-light-warning">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1">Doublons</h6>
                    <h4 class="text-warning mb-0">{{ number_format($import->duplicate_rows ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-light-info">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1">Montant total</h6>
                    <h4 class="text-info mb-0">{{ number_format($import->total_amount ?? 0, 2, ',', ' ') }}</h4>
                    <small class="text-muted">DH</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-light-secondary">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1">Taux succes</h6>
                    <h4 class="mb-0">{{ $import->getSuccessRate() }}%</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Totals: Mode paiement + Type frais --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0"><i class="ph-duotone ph-credit-card me-1"></i> Mode paiement</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mode paiement</th>
                                <th class="text-end">Montant</th>
                                <th class="text-end">Ops</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Encaissement::PAYMENT_METHODS as $key => $label)
                                @php $row = $byMethod->get($key); @endphp
                                @if($row)
                                <tr>
                                    <td>
                                        <span class="badge bg-light-{{ $key === 'especes' ? 'success' : ($key === 'tpe' ? 'primary' : ($key === 'cheque' ? 'warning' : 'info')) }} me-1">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($row->total, 2, ',', ' ') }} DH</td>
                                    <td class="text-end text-muted">{{ $row->count }}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td class="text-end">{{ number_format($import->total_amount ?? 0, 2, ',', ' ') }} DH</td>
                                <td class="text-end">{{ $import->success_rows ?? 0 }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0"><i class="ph-duotone ph-receipt me-1"></i> Type de frais</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Frais</th>
                                <th class="text-end">Montant</th>
                                <th class="text-end">Ops</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Encaissement::FEE_TYPES as $key => $label)
                                @php $row = $byFeeType->get($key); @endphp
                                @if($row)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-end fw-bold">{{ number_format($row->total, 2, ',', ' ') }} DH</td>
                                    <td class="text-end text-muted">{{ $row->count }}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td class="text-end">{{ number_format($import->total_amount ?? 0, 2, ',', ' ') }} DH</td>
                                <td class="text-end">{{ $import->success_rows ?? 0 }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Errors Log --}}
    @if($import->errors_log && count($import->errors_log) > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-danger">
                    <div class="card-header" id="errorsHeading">
                        <h5 class="mb-0">
                            <a class="text-danger" data-bs-toggle="collapse" href="#errorsCollapse" role="button" aria-expanded="false" aria-controls="errorsCollapse">
                                <i class="ph-duotone ph-warning me-1"></i>
                                Journal des erreurs ({{ count($import->errors_log) }})
                                <i class="ph-duotone ph-caret-down ms-1"></i>
                            </a>
                        </h5>
                    </div>
                    <div id="errorsCollapse" class="collapse">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 80px">Ligne</th>
                                            <th>Erreur</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($import->errors_log as $error)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-light-danger">
                                                        {{ $error['row'] ?? '—' }}
                                                    </span>
                                                </td>
                                                <td class="text-danger">{{ $error['message'] ?? $error }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Imported Encaissements Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <h5>Encaissements importes</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Etudiant</th>
                                    <th>Ref.</th>
                                    <th class="text-end">Montant</th>
                                    <th>Methode</th>
                                    <th>Type frais</th>
                                    <th>Operateur</th>
                                    <th>Groupe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($encaissements as $enc)
                                    <tr>
                                        <td class="text-muted">{{ $enc->order_number ?? $loop->iteration }}</td>
                                        <td class="text-nowrap">{{ $enc->collected_at->format('d/m/Y') }}</td>
                                        <td class="fw-semibold">{{ $enc->student_name }}</td>
                                        <td><span class="text-muted">{{ $enc->reference ?? '—' }}</span></td>
                                        <td class="text-end fw-semibold">{{ number_format($enc->amount, 2, ',', ' ') }}</td>
                                        <td>
                                            @if($enc->payment_method)
                                                <span class="badge bg-light-{{ $enc->payment_method === 'especes' ? 'success' : ($enc->payment_method === 'tpe' ? 'primary' : 'info') }}">
                                                    {{ $enc->getPaymentMethodLabel() }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $enc->getFeeTypeLabel() }}</td>
                                        <td>{{ ucfirst($enc->operator_name ?? '—') }}</td>
                                        <td>{{ $enc->group_name ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">Aucun encaissement dans cet import.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $encaissements->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const toastEl = document.getElementById('liveToast');
    if (toastEl) { new bootstrap.Toast(toastEl).show(); }
});
</script>
@endsection
