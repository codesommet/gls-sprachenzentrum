@extends('layouts.main')

@section('title', 'Gestion des Certificats')
@section('breadcrumb-item', 'Examens')
@section('breadcrumb-item-active', 'Certificats')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    {{-- Toast Notifications --}}
    @if (session('toast') || session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <img src="{{ asset('assets/images/favicon/favicon.svg') }}" class="img-fluid me-2" alt="favicon"
                        style="width: 17px">

                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Just now</small>

                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>

                <div class="toast-body">
                    {{ session('toast') ?? (session('success') ?? session('error')) }}
                </div>
            </div>
        </div>
    @endif


    {{-- Bulk PDF Export --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('backoffice.certificates.export.bulk-pdf') }}" method="GET"
                        class="row align-items-end g-3">
                        <div class="col-auto">
                            <label class="form-label fw-bold mb-1">Exporter en PDF</label>
                        </div>
                        <div class="col-auto">
                            <label for="from_id" class="form-label mb-1">De l'ID</label>
                            <input type="number" name="from_id" id="from_id" class="form-control" min="1"
                                required placeholder="ex: 1">
                        </div>
                        <div class="col-auto">
                            <label for="to_id" class="form-label mb-1">Jusqu'à l'ID</label>
                            <input type="number" name="to_id" id="to_id" class="form-control" min="1"
                                required placeholder="ex: 50">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-danger">
                                <i class="ti ti-file-type-pdf me-1"></i> Exporter PDF
                            </button>
                        </div>
                    </form>
                    @if ($errors->any())
                        <div class="text-danger mt-2 small">
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">

            <div class="card table-card">

                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h5 class="mb-3 mb-sm-0">Certificats</h5>
                        <a href="{{ route('backoffice.certificates.create') }}" class="btn btn-primary">Ajouter
                            Certificat</a>
                    </div>
                </div>

                <div class="card-body pt-3">
                    @include('backoffice.certificates.table')
                </div>

            </div>

        </div>
    </div>

@endsection

@section('scripts')
    <script type="module">
        import {
            DataTable
        } from "/build/js/plugins/module.js";
        window.dt = new DataTable("#pc-dt-simple", {
            perPage: 5,
            perPageSelect: [5, 10, 25, 50]
        });
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
