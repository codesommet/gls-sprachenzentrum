@extends('layouts.main')

@section('title', 'Inscriptions du Groupe')
@section('breadcrumb-item', 'Groupes')
@section('breadcrumb-item-active', 'Inscriptions')

{{-- Optional animation like the other page --}}
@section('page-animation', 'animate__fadeIn')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/animate.min.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-12">

      {{-- =========================
ALERTS (GLOBAL / DYNAMIC)
========================= --}}

{{-- Validation errors --}}
@if ($errors->any())
    <div class="alert alert-danger animate__animated animate__shakeX">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Status action (approve / reject) --}}
@if(session('status_action'))
    <div class="alert alert-{{ session('status_type', 'success') }}
                animate__animated
                {{ session('status_type') === 'danger' ? 'animate__shakeX' : 'animate__fadeInDown' }}">
        {{ session('status_action') }}
    </div>
@endif

{{-- Backward compatibility (old flashes) --}}
@if(!session('status_action'))
    @if(session('success'))
        <div class="alert alert-success animate__animated animate__fadeInDown">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning animate__animated animate__fadeInDown">
            {{ session('warning') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info animate__animated animate__fadeInDown">
            {{ session('info') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger animate__animated animate__shakeX">
            {{ session('error') }}
        </div>
    @endif
@endif


        <div class="card table-card animate__animated animate__fadeIn">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    Inscriptions – {{ $group->name }}
                </h5>

                <a href="{{ route('backoffice.groups.index') }}" class="btn btn-secondary">
                    Retour
                </a>
            </div>

            <div class="card-body">

                @if($applications instanceof \Illuminate\Support\Collection && $applications->isEmpty())
                    <div class="alert alert-info mb-0 animate__animated animate__fadeInDown">
                        Aucun étudiant inscrit pour le moment.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nom</th>
                                    <th>WhatsApp</th>
                                    <th>Email</th>
                                    <th>Birthday</th>
                                    <th>Date demande</th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($applications as $app)
                                    <tr>
                                        <td>{{ $app->id }}</td>
                                        <td>{{ $app->full_name ?? '-' }}</td>
                                        <td>{{ $app->whatsapp_number ?? '-' }}</td>
                                        <td>{{ $app->email ?? '-' }}</td>

                                        <td>
                                            {{ $app->birthday ? \Carbon\Carbon::parse($app->birthday)->format('d/m/Y') : '-' }}
                                        </td>

                                        <td>
                                            {{ $app->created_at ? \Carbon\Carbon::parse($app->created_at)->format('d/m/Y H:i') : '-' }}
                                        </td>

                                        <td>
                                            @php $status = $app->status ?? 'pending'; @endphp

                                            @if($status === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @endif
                                        </td>

                                        <td class="text-end">
                                            <form action="{{ route('backoffice.groups.applications.approve', [$group->id, $app->id]) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-success"
                                                        @disabled(($app->status ?? 'pending') === 'approved')>
                                                    Approve
                                                </button>
                                            </form>

                                            <form action="{{ route('backoffice.groups.applications.reject', [$group->id, $app->id]) }}"
                                                  method="POST"
                                                  class="d-inline ms-1">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-danger"
                                                        @disabled(($app->status ?? 'pending') === 'rejected')>
                                                    Disapprove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                    @if(method_exists($applications, 'links'))
                        <div class="mt-3">
                            {{ $applications->links() }}
                        </div>
                    @endif
                @endif

            </div>
        </div>

    </div>
</div>
@endsection
