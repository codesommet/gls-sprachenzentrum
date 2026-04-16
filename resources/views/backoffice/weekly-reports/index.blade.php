@extends('layouts.main')

@section('title', 'Rapport Semaine')
@section('breadcrumb-item', 'Pilotage')
@section('breadcrumb-item-active', 'Rapport Semaine')

@section('css')
<style>
    /* ===== Week Navigation ===== */
    .week-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .week-nav .btn { padding: 6px 12px; white-space: nowrap; font-size: 0.82rem; }
    .week-label {
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        min-width: 200px;
    }

    /* ===== Desktop: Table Calendar (>=992px) ===== */
    .week-calendar { width: 100%; border-collapse: separate; border-spacing: 0; }
    .week-calendar th {
        background: #4680ff;
        color: #fff;
        text-align: center;
        padding: 10px 6px;
        font-size: 0.82rem;
        font-weight: 600;
    }
    .week-calendar th:first-child { border-radius: 8px 0 0 0; }
    .week-calendar th:last-child  { border-radius: 0 8px 0 0; }

    .week-calendar td {
        border: 1px solid #e9ecef;
        vertical-align: top;
        padding: 8px;
        min-height: 140px;
        height: 140px;
        width: 20%;
        cursor: pointer;
        transition: background .15s;
        position: relative;
    }
    .week-calendar td:hover { background: #f0f4ff; }
    .week-calendar td.today { background: #f0f7ff; border-color: #4680ff; }

    .day-number {
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 6px;
        color: #333;
    }
    .today .day-number { color: #4680ff; }

    .btn-add-day {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: none;
        background: #4680ff;
        color: #fff;
        font-size: 16px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .15s;
    }
    .week-calendar td:hover .btn-add-day { opacity: 1; }

    /* ===== Report Chip (shared) ===== */
    .report-chip {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        background: #e8f0fe;
        border-left: 3px solid #4680ff;
        border-radius: 4px;
        padding: 6px 8px;
        margin-bottom: 4px;
        font-size: 0.78rem;
        line-height: 1.35;
        cursor: pointer;
        transition: background .15s;
    }
    .report-chip:hover { background: #d4e4fd; }
    .report-chip .teacher-name {
        font-weight: 600;
        color: #1a3a6e;
        white-space: nowrap;
    }
    .report-chip .notes-preview {
        color: #555;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ===== Mobile: Stacked Day Cards (<992px) ===== */
    .mobile-days { display: none; }

    .day-card {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 12px;
        cursor: pointer;
        transition: box-shadow .15s;
    }
    .day-card:active { box-shadow: 0 0 0 3px rgba(70,128,255,.25); }
    .day-card.today { border-color: #4680ff; }

    .day-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    .day-card.today .day-card-header { background: #edf3ff; }
    .day-card-header .day-label {
        font-weight: 700;
        font-size: 0.95rem;
        color: #333;
    }
    .day-card.today .day-card-header .day-label { color: #4680ff; }
    .day-card-header .btn-add-mobile {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        background: #4680ff;
        color: #fff;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .day-card-body {
        padding: 10px 14px;
        min-height: 50px;
    }
    .day-card-body .report-chip {
        font-size: 0.85rem;
        padding: 8px 10px;
    }
    .day-card-body .report-chip .notes-preview {
        max-width: none;
        white-space: normal;
    }
    .day-card-body .empty-label {
        color: #adb5bd;
        font-size: 0.82rem;
        font-style: italic;
    }

    /* ===== Responsive breakpoints ===== */
    @media (max-width: 991.98px) {
        .desktop-calendar { display: none !important; }
        .mobile-days { display: block !important; }
    }
    @media (min-width: 992px) {
        .desktop-calendar { display: block; }
        .mobile-days { display: none; }
    }

    /* Tablet tweaks */
    @media (min-width: 992px) and (max-width: 1199.98px) {
        .week-calendar td { padding: 6px; height: 120px; }
        .report-chip .notes-preview { max-width: 90px; }
        .week-calendar th { font-size: 0.75rem; padding: 8px 4px; }
    }

    /* Large desktop */
    @media (min-width: 1200px) {
        .report-chip .notes-preview { max-width: 140px; }
    }
</style>
@endsection

@section('content')

{{-- Toast --}}
@if (session('success') || session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
        <div id="liveToast" class="toast hide" role="alert">
            <div class="toast-header">
                <img src="{{ asset('assets/images/favicon/favicon.svg') }}" class="img-fluid me-2" alt="" style="width:17px">
                <strong class="me-auto">GLS Backoffice</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">{{ session('success') ?? session('error') }}</div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">

            {{-- Header with week navigation --}}
            <div class="card-header">
                <h5 class="mb-3 text-center text-lg-start">Rapport Semaine — Enseignants</h5>

                <div class="week-nav">
                    <a href="{{ route('backoffice.weekly_reports.index', ['week' => $date->copy()->subWeek()->format('Y-m-d')]) }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="ph-duotone ph-caret-left"></i> Préc.
                    </a>

                    <span class="week-label">
                        {{ $weekDays->first()->translatedFormat('d M') }} — {{ $weekDays->last()->translatedFormat('d M Y') }}
                    </span>

                    <a href="{{ route('backoffice.weekly_reports.index', ['week' => $date->copy()->addWeek()->format('Y-m-d')]) }}"
                       class="btn btn-outline-secondary btn-sm">
                        Suiv. <i class="ph-duotone ph-caret-right"></i>
                    </a>

                    @if (!$date->isCurrentWeek())
                        <a href="{{ route('backoffice.weekly_reports.index') }}" class="btn btn-primary btn-sm">Aujourd'hui</a>
                    @endif
                </div>
            </div>

            <div class="card-body p-2 p-md-3">

                {{-- ===== DESKTOP: Table Calendar ===== --}}
                <div class="desktop-calendar">
                    <table class="week-calendar">
                        <thead>
                            <tr>
                                @foreach ($weekDays as $day)
                                    <th>{{ $day->translatedFormat('l d/m') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach ($weekDays as $day)
                                    @php
                                        $key        = $day->format('Y-m-d');
                                        $isToday    = $day->isToday();
                                        $dayReports = $reports[$key] ?? collect();
                                    @endphp
                                    <td class="{{ $isToday ? 'today' : '' }}"
                                        data-date="{{ $key }}"
                                        onclick="openAddModal('{{ $key }}', '{{ $day->translatedFormat('l d M Y') }}')">

                                        <div class="day-number">{{ $day->format('d') }}</div>
                                        <button class="btn-add-day" title="Ajouter rapport">+</button>

                                        @foreach ($dayReports as $report)
                                            <div class="report-chip"
                                                 onclick="event.stopPropagation(); openEditModal({{ $report->id }}, {{ $report->teacher_id }}, '{{ $key }}', '{{ $day->translatedFormat('l d M Y') }}', {{ json_encode($report->notes) }})">
                                                <span class="teacher-name">{{ $report->teacher->name }}</span>
                                                <span class="notes-preview">{{ Str::limit($report->notes, 40) }}</span>
                                            </div>
                                        @endforeach
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ===== MOBILE / TABLET: Stacked Day Cards ===== --}}
                <div class="mobile-days">
                    @foreach ($weekDays as $day)
                        @php
                            $key        = $day->format('Y-m-d');
                            $isToday    = $day->isToday();
                            $dayReports = $reports[$key] ?? collect();
                        @endphp
                        <div class="day-card {{ $isToday ? 'today' : '' }}">
                            <div class="day-card-header">
                                <span class="day-label">{{ $day->translatedFormat('l d M') }}</span>
                                <button class="btn-add-mobile"
                                        onclick="event.stopPropagation(); openAddModal('{{ $key }}', '{{ $day->translatedFormat('l d M Y') }}')"
                                        title="Ajouter rapport">+</button>
                            </div>
                            <div class="day-card-body"
                                 onclick="openAddModal('{{ $key }}', '{{ $day->translatedFormat('l d M Y') }}')">

                                @forelse ($dayReports as $report)
                                    <div class="report-chip"
                                         onclick="event.stopPropagation(); openEditModal({{ $report->id }}, {{ $report->teacher_id }}, '{{ $key }}', '{{ $day->translatedFormat('l d M Y') }}', {{ json_encode($report->notes) }})">
                                        <span class="teacher-name">{{ $report->teacher->name }}</span>
                                        <span class="notes-preview">{{ Str::limit($report->notes, 80) }}</span>
                                    </div>
                                @empty
                                    <span class="empty-label">Aucun rapport — toucher pour ajouter</span>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ==================== MODAL: Add / Edit Report ==================== --}}
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <form id="reportForm" method="POST" action="{{ route('backoffice.weekly_reports.store') }}">
                @csrf
                <input type="hidden" name="report_date" id="modalDate">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Ajouter un rapport</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted mb-3" id="modalDateLabel"></p>

                    <div class="mb-3">
                        <label for="teacher_id" class="form-label">Enseignant</label>
                        <select name="teacher_id" id="teacher_id" class="form-select form-select-lg" required>
                            <option value="">— Sélectionner un enseignant —</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes / Activités</label>
                        <textarea name="notes" id="notes" class="form-control" rows="5"
                                  placeholder="Décrivez ce que l'enseignant fera ce jour..." required></textarea>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-danger btn-sm d-none" id="btnDelete"
                                onclick="deleteReport()">
                            <i class="ph-duotone ph-trash"></i> Supprimer
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete form (hidden) --}}
<form id="deleteForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
    const reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
    let currentReportId = null;

    function openAddModal(date, label) {
        currentReportId = null;
        document.getElementById('modalTitle').textContent = 'Ajouter un rapport';
        document.getElementById('modalDate').value = date;
        document.getElementById('modalDateLabel').textContent = label;
        document.getElementById('teacher_id').value = '';
        document.getElementById('notes').value = '';
        document.getElementById('btnDelete').classList.add('d-none');
        reportModal.show();
    }

    function openEditModal(id, teacherId, date, label, notes) {
        currentReportId = id;
        document.getElementById('modalTitle').textContent = 'Modifier le rapport';
        document.getElementById('modalDate').value = date;
        document.getElementById('modalDateLabel').textContent = label;
        document.getElementById('teacher_id').value = teacherId;
        document.getElementById('notes').value = notes;
        document.getElementById('btnDelete').classList.remove('d-none');
        reportModal.show();
    }

    function deleteReport() {
        if (!currentReportId) return;
        if (!confirm('Supprimer ce rapport ?')) return;

        const form = document.getElementById('deleteForm');
        form.action = '{{ url("backoffice/weekly-reports") }}/' + currentReportId;
        form.submit();
    }

    // Toast auto-show
    document.addEventListener('DOMContentLoaded', function () {
        const toastEl = document.getElementById('liveToast');
        if (toastEl) new bootstrap.Toast(toastEl).show();
    });
</script>
@endsection
