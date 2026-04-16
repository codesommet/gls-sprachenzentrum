@extends('layouts.main')

@section('title', 'Présence v' . $import->version . ' — ' . $group->name)
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-link', route('backoffice.payroll.presence.dashboard'))
@section('breadcrumb-item-active', 'Détails v' . $import->version)

@section('css')
    <style>
        .presence-cell {
            min-width: 36px;
            text-align: center;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px !important;
        }
        .presence-cell.present { background-color: #d4edda; color: #155724; }
        .presence-cell.absent { background-color: #f8d7da; color: #721c24; }
        .presence-cell.no_data { background-color: #f8f9fa; color: #6c757d; }
        .category-badge { font-size: 0.85rem; }
        .summary-card .display-6 { font-size: 2rem; }
        .category-row { cursor: pointer; transition: background-color 0.15s; }
        .category-row:hover { background-color: #e8f4fd !important; }
    </style>
@endsection

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Paiement Professeurs</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    @php
        $summary = $import->paymentSummary;
        $rate = $import->getEffectivePaymentPerStudent() ?? 0;
        $allDates = $import->students
            ->flatMap(fn($s) => $s->records->pluck('date'))
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values();
    @endphp

    {{-- Import Info --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-2">
                                {{ $group->name }}
                                <span class="badge bg-primary ms-2">v{{ $import->version }}</span>
                                @if($summary?->isApproved())
                                    <span class="badge bg-success ms-1">Approuvé</span>
                                @endif
                            </h5>
                            <div class="row text-muted">
                                <div class="col-auto"><strong>Professeur:</strong> {{ $group->teacher?->name ?? '—' }}</div>
                                <div class="col-auto"><strong>Niveau:</strong> {{ $group->level }}</div>
                                <div class="col-auto"><strong>Mois:</strong> {{ $import->month->translatedFormat('F Y') }}</div>
                                <div class="col-auto"><strong>Période:</strong> {{ $import->date_start->format('d/m') }} — {{ $import->date_end->format('d/m/Y') }}</div>
                                <div class="col-auto"><strong>Semaines:</strong> <span class="badge bg-light-info">{{ $import->total_weeks_label }}</span></div>
                                <div class="col-auto"><strong>Taux:</strong> {{ number_format($rate, 2) }} DH/étudiant</div>
                            </div>
                            @if($import->notes)
                                <div class="mt-2"><em>{{ $import->notes }}</em></div>
                            @endif
                        </div>
                        <div>
                            @if(!$summary?->isApproved())
                                <form action="{{ route('backoffice.payroll.presence.import.approve', $import) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Approuver ce paiement ?')">
                                    @csrf
                                    <button class="btn btn-success btn-sm">
                                        <i class="ph-duotone ph-check-circle me-1"></i> Approuver
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('backoffice.payroll.presence.import.recalculate', $import) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-warning btn-sm">
                                    <i class="ph-duotone ph-arrow-clockwise me-1"></i> Recalculer
                                </button>
                            </form>
                            <a href="{{ route('backoffice.payroll.presence.group.imports', $group) }}"
                               class="btn btn-outline-secondary btn-sm">
                                Historique
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Summary Cards --}}
    @if($summary)
        <div class="row mb-3">
            <div class="col-md-2">
                <div class="card summary-card text-center">
                    <div class="card-body py-3">
                        <div class="display-6 text-success">{{ $summary->count_full }}</div>
                        <small class="text-muted">Complet<br>{{ number_format(floor($rate * 1.00), 0) }} DH</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card summary-card text-center">
                    <div class="card-body py-3">
                        <div class="display-6 text-info">{{ $summary->count_three_quarter }}</div>
                        <small class="text-muted">3/4<br>{{ number_format(floor($rate * 0.75), 0) }} DH</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card summary-card text-center">
                    <div class="card-body py-3">
                        <div class="display-6 text-warning">{{ $summary->count_half }}</div>
                        <small class="text-muted">1/2<br>{{ number_format(floor($rate * 0.50), 0) }} DH</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card summary-card text-center">
                    <div class="card-body py-3">
                        <div class="display-6 text-secondary">{{ $summary->count_quarter }}</div>
                        <small class="text-muted">1/4<br>{{ number_format(floor($rate * 0.25), 0) }} DH</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card summary-card text-center">
                    <div class="card-body py-3">
                        <div class="display-6 text-danger">{{ $summary->count_zero }}</div>
                        <small class="text-muted">Zéro<br>0 DH</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card summary-card text-center bg-light-primary">
                    <div class="card-body py-3">
                        <div class="display-6 fw-bold">{{ number_format($summary->total_payment, 0) }}</div>
                        <small class="fw-bold">TOTAL DH</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Calculation Breakdown --}}
        @php
            // Group students by their effective category
            $studentsByCategory = $import->students->groupBy(fn($s) => $s->getEffectiveCategory());

            $lines = [
                ['full',          'Complet (1/1)', $summary->count_full,          floor($rate * 1.00)],
                ['three_quarter', '3/4',           $summary->count_three_quarter, floor($rate * 0.75)],
                ['half',          '1/2',           $summary->count_half,          floor($rate * 0.50)],
                ['quarter',       '1/4',           $summary->count_quarter,       floor($rate * 0.25)],
                ['zero',          'Zéro',          $summary->count_zero,          0],
            ];
        @endphp

        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Calcul du paiement</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-hover" style="max-width: 600px;">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th class="text-end">Nombre</th>
                                    <th class="text-end">Montant unitaire</th>
                                    <th class="text-end">Sous-total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lines as [$catKey, $label, $count, $unitAmount])
                                    @if($count > 0)
                                        <tr role="button"
                                            class="category-row"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal-{{ $catKey }}"
                                            title="Cliquez pour voir les étudiants">
                                            <td>
                                                {{ $label }}
                                                <i class="ph-duotone ph-eye ms-1 text-muted" style="font-size:0.8rem"></i>
                                            </td>
                                            <td class="text-end">{{ $count }}</td>
                                            <td class="text-end">{{ number_format($unitAmount, 0) }} DH</td>
                                            <td class="text-end">{{ number_format($count * $unitAmount, 0) }} DH</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-warning fw-bold">
                                    <td>Total</td>
                                    <td class="text-end">{{ $summary->total_students }}</td>
                                    <td></td>
                                    <td class="text-end">{{ number_format($summary->total_payment, 2) }} DH</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modals for each category --}}
        @foreach($lines as [$catKey, $label, $count, $unitAmount])
            @if($count > 0)
                <div class="modal fade" id="modal-{{ $catKey }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    {{ $label }} — {{ $count }} étudiant{{ $count > 1 ? 's' : '' }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-0">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">#</th>
                                            <th>Etudiant</th>
                                            <th class="text-center">P</th>
                                            <th class="text-center">A</th>
                                            <th class="text-center">Quarters</th>
                                            <th class="text-end pe-3">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(($studentsByCategory[$catKey] ?? collect())->sortBy('student_name')->values() as $idx => $student)
                                            <tr>
                                                <td class="ps-3">{{ $idx + 1 }}</td>
                                                <td>
                                                    <strong>{{ $student->student_name }}</strong>
                                                    @if($student->category_override)
                                                        <span class="badge bg-warning ms-1" title="Catégorie modifiée manuellement">modifié</span>
                                                    @endif
                                                </td>
                                                <td class="text-center text-success fw-bold">{{ $student->total_present }}</td>
                                                <td class="text-center text-danger fw-bold">{{ $student->total_absent }}</td>
                                                <td class="text-center">{{ $student->active_quarters }}/4</td>
                                                <td class="text-end pe-3 fw-bold">{{ number_format($student->weighted_amount, 0) }} DH</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer bg-light">
                                <div class="w-100 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">{{ $count }} x {{ number_format($unitAmount, 0) }} DH</span>
                                    <strong class="fs-5">{{ number_format($count * $unitAmount, 0) }} DH</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif

    {{-- Students Attendance Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Détail de présence — {{ $import->students->count() }} étudiants</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 40px">#</th>
                                    <th style="min-width: 180px">Etudiant</th>
                                    @foreach($allDates as $date)
                                        @php $d = \Carbon\Carbon::parse($date); @endphp
                                        <th class="presence-cell" title="{{ $d->format('d/m/Y') }}">
                                            <small>{{ mb_strtoupper(mb_substr($d->translatedFormat('D'), 0, 2)) }}</small>
                                            <br>{{ $d->format('d') }}
                                        </th>
                                    @endforeach
                                    <th class="text-center" style="min-width: 50px">P</th>
                                    <th class="text-center" style="min-width: 50px">A</th>
                                    <th class="text-center" style="min-width: 50px">Q</th>
                                    <th style="min-width: 100px">Catégorie</th>
                                    <th class="text-end" style="min-width: 90px">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($import->students->sortBy('row_number') as $student)
                                    @php
                                        $recordsByDate = $student->records->keyBy(fn($r) => $r->date->format('Y-m-d'));
                                        $effectiveCat = $student->getEffectiveCategory();
                                        $isOverridden = $student->category_override !== null;
                                    @endphp
                                    <tr>
                                        <td>{{ $student->row_number }}</td>
                                        <td>
                                            <strong>{{ $student->student_name }}</strong>
                                            @if($student->isCancelled())
                                                <span class="badge bg-danger ms-1">Annulé</span>
                                            @elseif($student->isTransferred())
                                                <span class="badge bg-secondary ms-1">Transféré</span>
                                            @endif
                                        </td>
                                        @foreach($allDates as $date)
                                            @php
                                                $record = $recordsByDate[$date] ?? null;
                                                $status = $record?->status ?? 'no_data';
                                                $display = match($status) {
                                                    'present' => 'P',
                                                    'absent' => 'A',
                                                    default => '-',
                                                };
                                            @endphp
                                            <td class="presence-cell {{ $status }}">{{ $display }}</td>
                                        @endforeach
                                        <td class="text-center fw-bold text-success">{{ $student->total_present }}</td>
                                        <td class="text-center fw-bold text-danger">{{ $student->total_absent }}</td>
                                        <td class="text-center">{{ $student->active_quarters }}/4</td>
                                        <td>
                                            <form action="{{ route('backoffice.payroll.presence.student.category', $student) }}"
                                                  method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <select name="category_override"
                                                        class="form-select form-select-sm category-badge {{ $isOverridden ? 'border-warning' : '' }}"
                                                        onchange="this.form.submit()"
                                                        style="width: 110px">
                                                    <option value="" {{ !$isOverridden ? 'selected' : '' }}>
                                                        {{ \App\Models\PresenceImportStudent::CATEGORY_LABELS[$student->category] }} (auto)
                                                    </option>
                                                    @foreach(\App\Models\PresenceImportStudent::CATEGORY_LABELS as $catKey => $catLabel)
                                                        @if($catKey !== $student->category)
                                                            <option value="{{ $catKey }}" {{ $student->category_override === $catKey ? 'selected' : '' }}>
                                                                {{ $catLabel }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </form>
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($student->weighted_amount, 0) }} DH
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
