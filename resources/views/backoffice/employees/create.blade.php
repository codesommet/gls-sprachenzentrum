@extends('layouts.main')

@section('title', 'Nouvel employé')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Nouvel employé')

@section('content')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Nouvel employé</h5>
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

                    <form action="{{ route('backoffice.employees.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Centre <span class="text-danger">*</span></label>
                                <select name="site_id" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach($sites as $s)
                                        <option value="{{ $s->id }}" {{ old('site_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Poste <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r }}" {{ old('role') == $r ? 'selected' : '' }}>{{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date d'embauche</label>
                                <input type="date" name="hired_at" class="form-control" value="{{ old('hired_at') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-check me-1"></i> Créer l'employé
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
