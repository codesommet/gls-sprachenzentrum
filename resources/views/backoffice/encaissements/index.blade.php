@extends('layouts.main')

@section('title', 'Encaissements')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-active', 'Liste')

@section('content')

    {{-- Toast --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Maintenant</small>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-sm-auto">
                    <label class="form-label fw-semibold">
                        <i class="ph-duotone ph-funnel me-1"></i> Filtres
                    </label>
                </div>
                <div class="col-12 col-sm">
                    <select name="site_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tous les centres</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                {{ $site->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <select name="payment_method" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Toutes méthodes</option>
                        @foreach(\App\Models\Encaissement::PAYMENT_METHODS as $key => $label)
                            <option value="{{ $key }}" {{ request('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <select name="fee_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tous types</option>
                        @foreach(\App\Models\Encaissement::FEE_TYPES as $key => $label)
                            <option value="{{ $key }}" {{ request('fee_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="Du" onchange="this.form.submit()">
                </div>
                <div class="col-12 col-sm">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="Au" onchange="this.form.submit()">
                </div>
                <div class="col-12 col-sm">
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Rechercher..." onchange="this.form.submit()">
                </div>
                @if(request()->hasAny(['site_id', 'payment_method', 'fee_type', 'date_from', 'date_to', 'search']))
                    <div class="col-12 col-sm-auto">
                        <a href="{{ route('backoffice.encaissements.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph-duotone ph-x me-1"></i> Reset
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Total Bar --}}
    @if($totalAmount > 0)
    <div class="alert alert-primary d-flex justify-content-between align-items-center mb-3">
        <span><i class="ph-duotone ph-calculator me-2"></i> Total filtré :</span>
        <strong>{{ number_format($totalAmount, 2, ',', ' ') }} DH ({{ $encaissements->total() }} opérations)</strong>
    </div>
    @endif

    {{-- Table --}}
    <div class="card table-card">
        <div class="card-header">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h5 class="mb-3 mb-sm-0">Encaissements</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('backoffice.encaissements.imports.create') }}" class="btn btn-primary btn-sm">
                        <i class="ph-duotone ph-upload me-1"></i> Importer
                    </a>
                    <a href="{{ route('backoffice.encaissements.create') }}" class="btn btn-outline-success btn-sm">
                        <i class="ph-duotone ph-plus me-1"></i> Saisie manuelle
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Étudiant</th>
                            <th>Réf.</th>
                            <th>Centre</th>
                            <th class="text-end">Montant</th>
                            <th>Méthode</th>
                            <th>Type frais</th>
                            <th>Opérateur</th>
                            <th>Source</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($encaissements as $enc)
                            <tr>
                                <td class="text-nowrap">{{ $enc->collected_at->format('d/m/Y') }}</td>
                                <td class="fw-semibold">{{ $enc->student_name }}</td>
                                <td><span class="text-muted">{{ $enc->reference }}</span></td>
                                <td>{{ $enc->site->name ?? '—' }}</td>
                                <td class="text-end fw-semibold">{{ number_format($enc->amount, 2, ',', ' ') }}</td>
                                <td>
                                    <span class="badge bg-light-{{ $enc->payment_method === 'especes' ? 'success' : ($enc->payment_method === 'tpe' ? 'primary' : 'info') }}">
                                        {{ $enc->getPaymentMethodLabel() }}
                                    </span>
                                </td>
                                <td>{{ $enc->getFeeTypeLabel() }}</td>
                                <td>{{ ucfirst($enc->operator_name ?? '—') }}</td>
                                <td>
                                    <span class="badge bg-light-{{ $enc->source_system === 'manual' ? 'warning' : 'secondary' }}">
                                        {{ $enc->source_system }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('backoffice.encaissements.show', $enc) }}" class="avtar avtar-xs btn-link-primary" title="Voir">
                                        <i class="ph-duotone ph-eye"></i>
                                    </a>
                                    <a href="{{ route('backoffice.encaissements.edit', $enc) }}" class="avtar avtar-xs btn-link-warning" title="Modifier">
                                        <i class="ph-duotone ph-pencil-simple"></i>
                                    </a>
                                    <form action="{{ route('backoffice.encaissements.destroy', $enc) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet encaissement ?')">
                                        @csrf @method('DELETE')
                                        <button class="avtar avtar-xs btn-link-danger border-0 bg-transparent" title="Supprimer">
                                            <i class="ph-duotone ph-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Aucun encaissement trouvé.</td>
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

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const toastEl = document.getElementById('liveToast');
    if (toastEl) { new bootstrap.Toast(toastEl).show(); }
});
</script>
@endsection
