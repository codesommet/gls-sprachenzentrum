@extends('layouts.main')

@section('title', 'Import dépenses #' . $import->id)
@section('breadcrumb-item', 'Charges')
@section('breadcrumb-item-active', 'Import #' . $import->id)

@section('content')
    @if (session('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header"><strong class="me-auto">Charges</strong></div>
                <div class="toast-body">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    {{-- Metadata --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="mb-2">
                        Import dépenses #{{ $import->id }}
                        <span class="badge bg-light-{{ $import->status === 'completed' ? 'success' : 'danger' }} ms-2">
                            {{ $import->status === 'completed' ? 'Terminé' : 'Échoué' }}
                        </span>
                    </h5>
                    <div class="row text-muted">
                        <div class="col-auto"><strong>Centre :</strong> {{ $import->site->name ?? '—' }}</div>
                        <div class="col-auto"><strong>Fichier :</strong> {{ $import->file_name }}</div>
                        <div class="col-auto"><strong>Date :</strong> {{ $import->created_at->format('d/m/Y H:i') }}</div>
                        @if($import->month)
                            <div class="col-auto">
                                <strong>Mois :</strong>
                                <span class="badge bg-primary">{{ \Carbon\Carbon::parse($import->month . '-01')->translatedFormat('F Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('backoffice.encaissements.expenses.imports.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                    </a>
                    <form action="{{ route('backoffice.encaissements.expenses.imports.destroy', $import) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm"><i class="ph-duotone ph-trash me-1"></i> Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card bg-light-primary"><div class="card-body text-center py-3">
                <h6 class="text-muted mb-1">Dépenses</h6>
                <h4 class="text-primary mb-0">{{ $import->success_rows }}</h4>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-danger"><div class="card-body text-center py-3">
                <h6 class="text-muted mb-1">Total</h6>
                <h4 class="text-danger mb-0">{{ number_format($import->total_amount, 0, ',', ' ') }} <small>DH</small></h4>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-success"><div class="card-body text-center py-3">
                <h6 class="text-muted mb-1">Erreurs</h6>
                <h4 class="text-success mb-0">{{ $import->error_rows }}</h4>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-info"><div class="card-body text-center py-3">
                <h6 class="text-muted mb-1">Taux succès</h6>
                <h4 class="mb-0">{{ $import->getSuccessRate() }}%</h4>
            </div></div>
        </div>
    </div>

    {{-- Summary by type --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header py-3"><h6 class="mb-0"><i class="ph-duotone ph-receipt me-1"></i> Par type de dépense</h6></div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Type</th><th class="text-end">Montant</th><th class="text-end">Ops</th></tr>
                        </thead>
                        <tbody>
                            @foreach($byType as $type => $row)
                            <tr>
                                <td>{{ \App\Models\SiteExpense::TYPES[$type] ?? ucfirst($type) }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->total, 2, ',', ' ') }} DH</td>
                                <td class="text-end text-muted">{{ $row->count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td class="text-end">{{ number_format($import->total_amount, 2, ',', ' ') }} DH</td>
                                <td class="text-end">{{ $import->success_rows }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Expenses table --}}
    <div class="card table-card">
        <div class="card-header"><h5>Dépenses importées</h5></div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Réf.</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Méthode</th>
                            <th>Opérateur</th>
                            <th class="text-end">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $exp)
                            <tr>
                                <td>{{ $exp->order_number ?? $loop->iteration }}</td>
                                <td><span class="text-muted">{{ $exp->reference ?? '—' }}</span></td>
                                <td>{{ \App\Models\SiteExpense::TYPES[$exp->type] ?? $exp->type }}</td>
                                <td class="text-nowrap">{{ $exp->expense_date?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ ucfirst($exp->payment_method ?? '—') }}</td>
                                <td>{{ $exp->operator_name ?? '—' }}</td>
                                <td class="text-end fw-semibold">{{ number_format($exp->amount, 2, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Aucune dépense.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $expenses->links() }}
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const t = document.getElementById('liveToast');
    if (t) new bootstrap.Toast(t).show();
});
</script>
@endsection
