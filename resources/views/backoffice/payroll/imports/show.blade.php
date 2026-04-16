@extends('layouts.main')

@section('title', 'Import v' . $import->version . ' — ' . $group->name)
@section('breadcrumb-item', 'Suivi Paiement')
@section('breadcrumb-item-link', route('backoffice.payroll.dashboard'))
@section('breadcrumb-item-active', 'Détails import v' . $import->version)

@section('css')
    <style>
        .payment-cell { min-width: 80px; text-align: right; font-size: 0.85rem; }
    </style>
@endsection

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Suivi Paiement</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

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
                            </h5>
                            <div class="row text-muted">
                                <div class="col-auto"><strong>Fichier:</strong> {{ $import->file_name }}</div>
                                <div class="col-auto"><strong>Mois début:</strong> {{ $import->start_month->format('m/Y') }}</div>
                                <div class="col-auto"><strong>Importé:</strong> {{ $import->created_at->format('d/m/Y H:i') }}</div>
                                <div class="col-auto"><strong>Etudiants:</strong> {{ $import->students->count() }}</div>
                                @php $rate = $import->getEffectivePaymentPerStudent(); @endphp
                                @if($rate)
                                    <div class="col-auto"><strong>Taux:</strong> {{ number_format($rate, 2) }} DH/étudiant</div>
                                @endif
                            </div>
                            @if($import->notes)
                                <div class="mt-2"><em>{{ $import->notes }}</em></div>
                            @endif
                        </div>
                        <div>
                            @if($import->version > 1)
                                <a href="{{ route('backoffice.payroll.import.compare', ['group' => $group->id, 'import' => $import->id]) }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="ph-duotone ph-arrows-left-right me-1"></i> Comparer
                                </a>
                            @endif
                            <a href="{{ route('backoffice.payroll.group.imports', $group) }}"
                               class="btn btn-outline-secondary btn-sm">
                                Historique
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Students Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Etudiants importés</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        @php
                            // Collect all months in order
                            $allMonths = $import->students
                                ->flatMap(fn($s) => $s->payments->pluck('month'))
                                ->map(fn($m) => $m->format('Y-m'))
                                ->unique()
                                ->sort()
                                ->values();

                            // --- Build unified column order matching the Excel ---
                            // Get fee columns with their original col_index from first student
                            $firstStudent = $import->students->sortBy('row_number')->first();
                            $studentFees = $firstStudent?->fee_columns ?? [];
                            $hasFeeColumns = !empty($studentFees) && isset($studentFees[0]['col_index']);

                            $orderedColumns = collect();

                            if ($hasFeeColumns) {
                                // Add fee columns at their original positions
                                $feePositions = [];
                                foreach ($studentFees as $idx => $fc) {
                                    $pos = $fc['col_index'];
                                    $feePositions[] = $pos;
                                    $orderedColumns->push([
                                        'type'      => 'fee',
                                        'fee_index' => $idx,
                                        'pos'       => $pos,
                                        'header'    => $fc['header'],
                                    ]);
                                }

                                // Add months — fill positions that are not taken by fees
                                $minPos = min($feePositions);
                                $cursor = $minPos;
                                foreach ($allMonths as $month) {
                                    while (in_array($cursor, $feePositions)) $cursor++;
                                    $orderedColumns->push([
                                        'type'  => 'month',
                                        'month' => $month,
                                        'pos'   => $cursor,
                                    ]);
                                    $cursor++;
                                }

                                $orderedColumns = $orderedColumns->sortBy('pos')->values();
                            } else {
                                // Fallback: old imports without fee_columns col_index
                                // Show registration_fee first, then months
                                $orderedColumns->push(['type' => 'legacy_fee', 'pos' => 0]);
                                foreach ($allMonths as $month) {
                                    $orderedColumns->push(['type' => 'month', 'month' => $month, 'pos' => $orderedColumns->count()]);
                                }
                            }

                            // Prepare totals per column
                            $columnTotals = [];
                            foreach ($orderedColumns as $colKey => $col) {
                                $columnTotals[$colKey] = 0.0;
                            }
                        @endphp

                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 40px">#</th>
                                    <th style="min-width: 180px">Etudiant</th>
                                    <th style="min-width: 80px">Statut</th>
                                    @foreach($orderedColumns as $col)
                                        <th class="payment-cell" style="min-width: 90px">
                                            @if($col['type'] === 'fee')
                                                {{ $col['header'] }}
                                            @elseif($col['type'] === 'month')
                                                {{ \Carbon\Carbon::createFromFormat('Y-m', $col['month'])->translatedFormat('M Y') }}
                                            @else
                                                Inscription
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($import->students->sortBy('row_number') as $student)
                                    @php
                                        $paymentsByMonth = $student->payments->keyBy(fn($p) => $p->month->format('Y-m'));
                                        $rowBg = $student->row_color;
                                        $sFees = $student->fee_columns ?? [];
                                    @endphp
                                    <tr style="{{ $rowBg ? 'background-color:' . e($rowBg) . '22' : '' }}">
                                        <td>{{ $student->row_number }}</td>
                                        <td style="{{ $rowBg ? 'background-color:' . e($rowBg) . '33' : '' }}">
                                            <strong>{{ $student->student_name }}</strong>
                                        </td>
                                        <td>
                                            <form action="{{ route('backoffice.payroll.student.status', $student) }}"
                                                  method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <select name="status" class="form-select form-select-sm"
                                                        onchange="this.form.submit()" style="width: 120px">
                                                    <option value="active" {{ $student->status === 'active' ? 'selected' : '' }}>Actif</option>
                                                    <option value="transferred" {{ in_array($student->status, ['transferred', 'unknown']) ? 'selected' : '' }}>Archivé</option>
                                                    <option value="cancelled" {{ $student->status === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                                                </select>
                                            </form>
                                        </td>
                                        @foreach($orderedColumns as $colKey => $col)
                                            @if($col['type'] === 'fee')
                                                @php
                                                    $fee = $sFees[$col['fee_index']] ?? null;
                                                    $feeAmt = (float) ($fee['amount'] ?? 0);
                                                    $feeColor = $fee['color'] ?? null;
                                                    $columnTotals[$colKey] += $feeAmt;
                                                @endphp
                                                <td class="payment-cell"
                                                    style="{{ $feeColor ? 'background-color:' . e($feeColor) : '' }}">
                                                    {{ $feeAmt > 0 ? number_format($feeAmt, 2) : '—' }}
                                                </td>
                                            @elseif($col['type'] === 'month')
                                                @php
                                                    $payment = $paymentsByMonth[$col['month']] ?? null;
                                                    $amount = $payment ? (float) $payment->amount : 0;
                                                    $cellColor = $payment?->background_color;
                                                    $columnTotals[$colKey] += $amount;
                                                @endphp
                                                <td class="payment-cell"
                                                    style="{{ $cellColor ? 'background-color:' . e($cellColor) : '' }}">
                                                    {{ $amount > 0 ? number_format($amount, 2) : '0.00' }}
                                                </td>
                                            @else
                                                {{-- legacy_fee fallback --}}
                                                @php
                                                    $regFee = (float) $student->registration_fee;
                                                    $columnTotals[$colKey] += $regFee;
                                                @endphp
                                                <td class="payment-cell">
                                                    {{ $regFee > 0 ? number_format($regFee, 2) : '—' }}
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-warning fw-bold">
                                    <td></td>
                                    <td>Total</td>
                                    <td></td>
                                    @foreach($orderedColumns as $colKey => $col)
                                        <td class="payment-cell">{{ number_format($columnTotals[$colKey], 2) }}</td>
                                    @endforeach
                                </tr>
                            </tfoot>
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
