@extends('frontoffice.layouts.app')

@section('title', __('certificate.page_title'))

@section('content')

<style>
    body{
        background-color: var(--light--off-white) !important;
    }
</style>

    {{-- TOASTS (popups en haut à droite) --}}
    @if (session('certificate_error') || session('certificate_success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            @if (session('certificate_error'))
                <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive"
                    aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            {{ session('certificate_error') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if (session('certificate_success'))
                <div class="toast align-items-center text-bg-success border-0 show mt-2" role="alert"
                    aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            {{ __('certificate.success_toast') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="container py-5">

        <div class="text-center mb-5">
            <h1 class="fw-bold">{{ __('certificate.heading') }}</h1>
            <p class="text-muted">{{ __('certificate.subheading') }}</p>
        </div>

        {{-- FORMULAIRE --}}
        <div class="row justify-content-center">
            <div class="col-md-6">

                <form action="{{ route('front.certificate.check.post') }}" method="POST" class="card p-4 shadow-sm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">{{ __('certificate.form_label') }}</label>
                        <input type="text" name="certificate_number" class="form-control" placeholder="{{ __('certificate.form_placeholder') }}"
                            required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        {{ __('certificate.submit') }}
                    </button>
                </form>

            </div>
        </div>

        {{-- DÉTAILS DU CERTIFICAT SI TROUVÉ --}}
@php
    $cert = session('certificate_success');
    $publicToken = is_array($cert) ? ($cert['public_token'] ?? null) : null;
@endphp

@if ($cert)
    <div class="row justify-content-center mt-4">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-body">

                    <div class="row align-items-center">
                        {{-- LEFT : CERTIFICATE INFO --}}
                        <div class="col-md-8">
                            <h4 class="mb-3">{{ __('certificate.details_title') }}</h4>

                            <p><strong>{{ __('certificate.label_name') }} :</strong> {{ $cert['first_name'] }} {{ $cert['last_name'] }}</p>

                            <p><strong>{{ __('certificate.label_level') }} :</strong> {{ $cert['exam_level'] }}</p>

                            <p><strong>{{ __('certificate.label_exam_date') }} :</strong>
                                {{ \Carbon\Carbon::parse($cert['exam_date'])->format('d/m/Y') }}
                            </p>

                            <p><strong>{{ __('certificate.label_issued_date') }} :</strong>
                                {{ \Carbon\Carbon::parse($cert['issued_date'])->format('d/m/Y') }}
                            </p>

                            <p><strong>{{ __('certificate.label_number') }} :</strong> {{ $cert['certificate_number'] }}</p>

                            @if($publicToken)
                                <a href="{{ route('certificates.public.download', ['token' => $publicToken]) }}"
                                   target="_blank"
                                   class="btn btn-dark mt-3">
                                    {{ __('certificate.download_pdf') }}
                                </a>
                            @else
                                <div class="alert alert-warning mt-3 mb-0">
                                    {{ __('certificate.token_missing') }}
                                </div>
                            @endif
                        </div>

                        {{-- RIGHT : QR CODE --}}
                        <div class="col-md-4 text-center">
                            <div class="border rounded p-3">
                                @if($publicToken)
                                    <img
                                        src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(route('certificates.public.download', ['token' => $publicToken])) }}"
                                        alt="QR Code Certificat"
                                        class="img-fluid"
                                    >
                                    <div class="text-muted mt-2" style="font-size: 13px;">
                                        {{ __('certificate.qr_caption_line1') }}<br>{{ __('certificate.qr_caption_line2') }}
                                    </div>
                                @else
                                    <div class="text-muted" style="font-size: 13px;">
                                        {{ __('certificate.qr_unavailable') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endif


    </div>

@endsection
