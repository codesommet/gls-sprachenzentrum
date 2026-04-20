@extends('layouts.main')

@section('title', 'Primes')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-active', 'Primes')

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
                    <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tous les employes</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvee</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    </select>
                </div>
                @if(request()->hasAny(['site_id', 'user_id', 'status']))
                    <div class="col-12 col-sm-auto">
                        <a href="{{ route('backoffice.encaissements.primes.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph-duotone ph-x me-1"></i> Reset
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Info banner --}}
    <div class="alert alert-info mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="ph-duotone ph-robot me-1"></i>
                <strong>Primes automatiques</strong> — Les primes sont générées automatiquement depuis
                <a href="{{ route('backoffice.encaissements.recouvrement') }}">Recouvrement & Impayés</a>,
                basées sur la performance de recouvrement de chaque centre.
            </div>
            <a href="{{ route('backoffice.encaissements.primes.config') }}" class="btn btn-sm btn-outline-primary">
                <i class="ph-duotone ph-gear me-1"></i> Configuration
            </a>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-light-primary"><div class="card-body text-center py-3">
                <h6 class="text-muted mb-1">Total primes (filtre)</h6>
                <h4 class="text-primary mb-0">{{ number_format($totalAmount, 2, ',', ' ') }} <small>DH</small></h4>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-info"><div class="card-body text-center py-3">
                <h6 class="text-muted mb-1">Primes auto-générées</h6>
                <h4 class="text-info mb-0">{{ $autoCount }} / {{ $primes->total() }}</h4>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-success"><div class="card-body text-center py-3">
                <h6 class="text-muted mb-1">En attente d'approbation</h6>
                <h4 class="text-success mb-0">{{ $primes->where('approved_at', null)->count() }}</h4>
            </div></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card table-card">
        <div class="card-header">
            <h5 class="mb-0">Liste des primes</h5>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Période</th>
                            <th>Employé</th>
                            <th>Centre</th>
                            <th class="text-center">Source</th>
                            <th class="text-center">Taux</th>
                            <th class="text-end">Montant</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($primes as $prime)
                            <tr>
                                <td class="text-nowrap">
                                    @if($prime->period_start && $prime->period_end)
                                        <strong>{{ \Carbon\Carbon::parse($prime->period_start)->format('m/Y') }}</strong>
                                        @if($prime->period_months > 1)
                                            → {{ \Carbon\Carbon::parse($prime->period_end)->format('m/Y') }}
                                        @endif
                                        <br>
                                        <small class="badge bg-light-secondary">{{ $prime->period_months }} mois</small>
                                    @else
                                        {{ \Carbon\Carbon::parse($prime->month)->format('m/Y') }}
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $prime->user->name ?? '—' }}</td>
                                <td>{{ $prime->site->name ?? '—' }}</td>
                                <td class="text-center">
                                    @if($prime->auto_generated)
                                        <span class="badge bg-light-info" title="{{ $prime->reason }}">
                                            <i class="ph-duotone ph-robot"></i> Auto
                                        </span>
                                    @else
                                        <span class="badge bg-light-secondary">Manuelle</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($prime->collection_rate)
                                        <span class="badge bg-{{ $prime->collection_rate >= 70 ? 'success' : 'warning' }}">
                                            {{ number_format($prime->collection_rate, 1) }}%
                                        </span>
                                    @else — @endif
                                </td>
                                <td class="text-end fw-bold">{{ number_format($prime->amount, 2, ',', ' ') }} DH</td>
                                <td>
                                    @if($prime->approved_at)
                                        <span class="badge bg-success" title="Par {{ $prime->approvedBy->name ?? '—' }} le {{ $prime->approved_at->format('d/m/Y') }}">
                                            <i class="ph-duotone ph-check-circle"></i> Approuvée
                                        </span>
                                    @else
                                        <span class="badge bg-light-warning">En attente</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if(!$prime->approved_at)
                                        <form action="{{ route('backoffice.encaissements.primes.approve', $prime) }}" method="POST" class="d-inline" onsubmit="return confirm('Approuver cette prime ?')">
                                            @csrf
                                            <button class="avtar avtar-xs btn-link-success border-0 bg-transparent" title="Approuver">
                                                <i class="ph-duotone ph-check-circle"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('backoffice.encaissements.primes.destroy', $prime) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette prime ?')">
                                            @csrf @method('DELETE')
                                            <button class="avtar avtar-xs btn-link-danger border-0 bg-transparent" title="Rejeter">
                                                <i class="ph-duotone ph-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Verrouillée</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Aucune prime générée. Allez dans <a href="{{ route('backoffice.encaissements.recouvrement') }}">Recouvrement</a> pour générer des primes automatiques.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $primes->links() }}</div>
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
