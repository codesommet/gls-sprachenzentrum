@extends('layouts.main')

@section('title', 'Performance operateurs')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-active', 'Operateurs')

@section('content')

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
                    <select name="site_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les centres</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                {{ $site->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <input type="month" name="month" class="form-control" value="{{ request('month', now()->format('Y-m')) }}" onchange="this.form.submit()">
                </div>
                @if(request()->hasAny(['site_id', 'month']))
                    <div class="col-12 col-sm-auto">
                        <a href="{{ route('backoffice.encaissements.operators') }}" class="btn btn-outline-secondary">
                            <i class="ph-duotone ph-x me-1"></i> Reset
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card table-card">
        <div class="card-header">
            <h5 class="mb-0">Performance operateurs</h5>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Operateur</th>
                            <th class="text-end">Total collecte</th>
                            <th class="text-end">Operations</th>
                            <th class="text-end">Jours actifs</th>
                            <th class="text-end">Moy. / jour</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($operators as $index => $op)
                            <tr>
                                <td>
                                    @if($index === 0)
                                        <span class="badge bg-warning text-dark">#1</span>
                                    @elseif($index === 1)
                                        <span class="badge bg-secondary">#2</span>
                                    @elseif($index === 2)
                                        <span class="badge bg-light-warning">#3</span>
                                    @else
                                        <span class="text-muted">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ ucfirst($op->operator_name ?? $op['operator_name'] ?? '—') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($op->total_collected ?? $op['total_collected'] ?? 0, 2, ',', ' ') }} DH</td>
                                <td class="text-end">{{ $op->operations_count ?? $op['operations_count'] ?? 0 }}</td>
                                <td class="text-end">{{ $op->active_days ?? $op['active_days'] ?? 0 }}</td>
                                <td class="text-end">{{ number_format($op->avg_per_day ?? $op['avg_per_day'] ?? 0, 2, ',', ' ') }} DH</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aucun operateur trouve pour cette periode.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
