@extends('layouts.main')

@section('title', 'Campagnes WhatsApp')
@section('breadcrumb-item', 'Communication')
@section('breadcrumb-item-active', 'Campagnes WhatsApp')

@section('content')
    @if (session('success') || session('error'))
        <div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
            {{ session('success') ?? session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between gap-2 flex-wrap">
                        <h5 class="mb-3 mb-sm-0">Historique des campagnes WhatsApp</h5>

                        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap mb-3 mb-sm-0">
                            <label class="form-label small mb-0 fw-semibold text-nowrap">
                                <i class="ph-duotone ph-buildings me-1"></i> Centre :
                            </label>
                            <select name="site_id" class="form-select form-select-sm" style="min-width: 200px;"
                                    onchange="this.form.submit()">
                                <option value="all" {{ $siteId === 'all' ? 'selected' : '' }}>— Tous les centres —</option>
                                <option value="none" {{ $siteId === 'none' ? 'selected' : '' }}>— Non assigné —</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}"
                                        {{ (string) $siteId === (string) $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        <a href="{{ route('backoffice.whatsapp_campaigns.create') }}" class="btn btn-primary">
                            <i class="ph-duotone ph-plus-circle me-1"></i> Nouvelle campagne
                        </a>
                    </div>
                </div>

                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nom</th>
                                    <th>Centre</th>
                                    <th>Créée par</th>
                                    <th>Statut</th>
                                    <th>Total</th>
                                    <th>Envoyés</th>
                                    <th>Échecs</th>
                                    <th>Progression</th>
                                    <th>Créée le</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($campaigns as $c)
                                    @php
                                        $pct = $c->total ? round((($c->sent + $c->failed) * 100) / $c->total) : 0;
                                        $badge = [
                                            'queued'    => 'bg-secondary',
                                            'running'   => 'bg-info',
                                            'paused'    => 'bg-warning',
                                            'completed' => 'bg-success',
                                            'stopped'   => 'bg-dark',
                                        ][$c->status] ?? 'bg-light-secondary';
                                    @endphp
                                    <tr>
                                        <td>{{ $c->id }}</td>
                                        <td>
                                            <a href="{{ route('backoffice.whatsapp_campaigns.show', $c) }}" class="fw-semibold">
                                                {{ $c->name }}
                                            </a>
                                        </td>
                                        <td>{{ $c->site?->name ?? '—' }}</td>
                                        <td>{{ $c->user?->name ?? '—' }}</td>
                                        <td><span class="badge {{ $badge }}">{{ strtoupper($c->status) }}</span></td>
                                        <td>{{ $c->total }}</td>
                                        <td class="text-success fw-semibold">{{ $c->sent }}</td>
                                        <td class="text-danger fw-semibold">{{ $c->failed }}</td>
                                        <td style="min-width: 160px">
                                            <div class="progress" style="height: 8px">
                                                <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $pct }}%</small>
                                        </td>
                                        <td>{{ $c->created_at?->format('d/m/Y H:i') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('backoffice.whatsapp_campaigns.show', $c) }}"
                                               class="btn btn-sm btn-light-primary">Voir</a>
                                            <form action="{{ route('backoffice.whatsapp_campaigns.destroy', $c) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Supprimer cette campagne ? Si elle est en cours, elle sera d\'abord arrêtée.')">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="force" value="1">
                                                <button class="btn btn-sm btn-light-danger">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">
                                            Aucune campagne pour le moment.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
