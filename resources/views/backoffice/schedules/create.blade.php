@extends('layouts.main')

@section('title', 'Nouvelle entrée planning')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Nouvelle entrée')

@section('content')

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1">Nouvelle entrée planning</h5>
                            <p class="text-muted mb-0 small">Planifiez une période. Les samedis et dimanches sont exclus automatiquement.</p>
                        </div>
                        <a href="{{ route('backoffice.schedules.index') }}" class="btn btn-outline-secondary">
                            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form action="{{ route('backoffice.schedules.store') }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Employé <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ old('user_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }} ({{ $emp->site?->name ?? '—' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date début <span class="text-danger">*</span></label>
                                <input type="date" name="date_from" id="date_from" class="form-control calc-trigger" value="{{ old('date_from', now()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date fin <span class="text-danger">*</span></label>
                                <input type="date" name="date_to" id="date_to" class="form-control calc-trigger" value="{{ old('date_to', now()->toDateString()) }}" required>
                                <small class="text-muted">Même date = 1 jour</small>
                            </div>
                        </div>

                        <div id="daysPreview" class="alert alert-info d-none mb-4">
                            <i class="ph-duotone ph-calendar-check me-1"></i>
                            <strong id="daysCount">0</strong> jour(s) ouvré(s)
                            <span class="text-primary" id="daysExcluded"></span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label text-primary fw-semibold">Début</label>
                                <input type="time" name="start_time" id="start_time" class="form-control calc-trigger" value="{{ old('start_time', '09:00') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-primary fw-semibold">Fin</label>
                                <input type="time" name="end_time" id="end_time" class="form-control calc-trigger" value="{{ old('end_time', '18:00') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-warning fw-semibold">Pause début</label>
                                <input type="time" name="break_start" id="break_start" class="form-control calc-trigger" value="{{ old('break_start', '13:00') }}">
                                <small class="text-muted">Optionnel</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-warning fw-semibold">Pause fin</label>
                                <input type="time" name="break_end" id="break_end" class="form-control calc-trigger" value="{{ old('break_end', '14:00') }}">
                                <small class="text-muted">Optionnel</small>
                            </div>
                        </div>

                        {{-- Live calculation display --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light text-center">
                                    <div class="card-body py-3">
                                        <p class="text-muted mb-1 small text-uppercase">Amplitude</p>
                                        <h3 class="mb-0 text-primary fw-bold" id="spanDisplay">9h00</h3>
                                        <small class="text-muted" id="spanDetail">09:00 — 18:00</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light text-center">
                                    <div class="card-body py-3">
                                        <p class="text-muted mb-1 small text-uppercase">Pause</p>
                                        <h3 class="mb-0 text-warning fw-bold" id="breakDisplay">1h00</h3>
                                        <small class="text-muted" id="breakDetail">13:00 — 14:00</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success text-center">
                                    <div class="card-body py-3">
                                        <p class="text-success mb-1 small text-uppercase fw-semibold">Travaillé / jour</p>
                                        <h2 class="mb-0 text-success fw-bold" id="workedDisplay">8h00</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-primary text-center">
                                    <div class="card-body py-3">
                                        <p class="text-primary mb-1 small text-uppercase fw-semibold">Total période</p>
                                        <h2 class="mb-0 text-primary fw-bold" id="totalPeriodDisplay">8h00</h2>
                                        <small class="text-muted" id="totalPeriodDetail">1 jour x 8h00</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph-duotone ph-check me-1"></i> Enregistrer le planning
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
function timeToMinutes(t){if(!t)return 0;const[h,m]=t.split(':').map(Number);return h*60+m}
function fmt(mins){if(mins<0)mins=0;return Math.floor(mins/60)+'h'+String(mins%60).padStart(2,'0')}
function countWorkdays(f,t){if(!f||!t)return{w:0,we:0};let d=new Date(f),e=new Date(t),w=0,we=0;while(d<=e){const dow=d.getDay();dow===0||dow===6?we++:w++;d.setDate(d.getDate()+1)}return{w,we}}
function recalc(){
    const s=document.getElementById('start_time').value,e=document.getElementById('end_time').value,bs=document.getElementById('break_start').value,be=document.getElementById('break_end').value,df=document.getElementById('date_from').value,dt=document.getElementById('date_to').value;
    const span=Math.max(0,timeToMinutes(e)-timeToMinutes(s));let brk=0;if(bs&&be)brk=Math.max(0,timeToMinutes(be)-timeToMinutes(bs));const worked=Math.max(0,span-brk);
    document.getElementById('spanDisplay').textContent=fmt(span);document.getElementById('spanDetail').textContent=(s||'--:--')+' — '+(e||'--:--');
    document.getElementById('breakDisplay').textContent=brk>0?fmt(brk):'0h00';document.getElementById('breakDetail').textContent=bs&&be?bs+' — '+be:'Pas de pause';
    document.getElementById('workedDisplay').textContent=fmt(worked);
    const{w,we}=countWorkdays(df,dt);document.getElementById('totalPeriodDisplay').textContent=fmt(worked*w);document.getElementById('totalPeriodDetail').textContent=w+' j x '+fmt(worked);
    const p=document.getElementById('daysPreview'),dc=document.getElementById('daysCount'),de=document.getElementById('daysExcluded');
    if(df&&dt){p.classList.remove('d-none');dc.textContent=w;de.textContent=we>0?'('+we+' sam/dim exclus)':''}
}
document.querySelectorAll('.calc-trigger').forEach(el=>{el.addEventListener('input',recalc);el.addEventListener('change',recalc)});
recalc();
</script>
@endsection
