@extends('layouts.main')

@section('title', 'Gestion des Rôles')
@section('breadcrumb-item', 'Administration')
@section('breadcrumb-item-active', 'Rôles & Permissions')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    {{-- Toast Notifications --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <img src="{{ asset('assets/images/favicon/favicon.svg') }}"
                         class="img-fluid me-2" alt="favicon" style="width: 17px">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h5 class="mb-3 mb-sm-0">Rôles & Permissions</h5>
                        @can('roles.create')
                        <a href="{{ route('backoffice.roles.create') }}" class="btn btn-primary">Ajouter un rôle</a>
                        @endcan
                    </div>
                </div>

                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Nom du rôle</th>
                                    <th>Permissions</th>
                                    <th>Utilisateurs</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>{{ $role->id }}</td>
                                        <td>
                                            <span class="badge bg-light-primary">{{ $role->name }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light-info">{{ $role->permissions->count() }} permissions</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light-secondary">{{ $role->users->count() }}</span>
                                        </td>
                                        <td>
                                            @can('roles.edit')
                                            @if($role->name !== 'Super Admin' || auth()->user()->hasRole('Super Admin'))
                                            <a href="{{ route('backoffice.roles.edit', $role->id) }}"
                                               class="avtar avtar-xs btn-link-secondary me-2" title="Modifier">
                                                <i class="ti ti-edit f-20"></i>
                                            </a>
                                            @endif
                                            @endcan

                                            @can('roles.delete')
                                            @if($role->name !== 'Super Admin' && auth()->user()->hasRole('Super Admin'))
                                                <form action="{{ route('backoffice.roles.destroy', $role->id) }}"
                                                      method="POST" class="d-inline-block">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                                            onclick="return confirm('Supprimer ce rôle ?')" title="Supprimer">
                                                        <i class="ti ti-trash f-20"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Aucun rôle trouvé.</td>
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

@section('scripts')
    <script type="module">
        import { DataTable } from "/build/js/plugins/module.js";
        window.dt = new DataTable("#pc-dt-simple");
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) {
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>
@endsection
