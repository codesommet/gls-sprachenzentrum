@extends('layouts.main')

@section('title', 'Modifier employé')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Modifier employé')

@section('content')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Modifier : {{ $employee->name }}</h5>
                        <a href="{{ route('backoffice.employees.index') }}" class="btn btn-outline-secondary">
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

                    <form action="{{ route('backoffice.employees.update', $employee) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Centre <span class="text-danger">*</span></label>
                                <select name="site_id" class="form-select" required>
                                    @foreach($sites as $s)
                                        <option value="{{ $s->id }}" {{ old('site_id', $employee->site_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Poste <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    @foreach($roles as $r)
                                        <option value="{{ $r }}" {{ old('role', $employee->role) == $r ? 'selected' : '' }}>{{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date d'embauche</label>
                                <input type="date" name="hired_at" class="form-control" value="{{ old('hired_at', $employee->hired_at?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" {{ $employee->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Actif</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $employee->notes) }}</textarea>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-check me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
