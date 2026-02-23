@extends('errors.layout')

@section('title', '429 - Too Many Requests')

@section('content')
    <div class="error-icon" style="background: linear-gradient(135deg, #14b8a6, #0d9488);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
    </div>

    <div class="error-code">429</div>

    <h1 class="error-title">
        @if (app()->getLocale() == 'fr')
            Trop de requêtes
        @else
            Too Many Requests
        @endif
    </h1>

    <p class="error-message">
        @if (app()->getLocale() == 'fr')
            Vous avez envoyé trop de requêtes en peu de temps. Veuillez patienter quelques instants avant de réessayer.
        @else
            You've sent too many requests in a short period. Please wait a moment before trying again.
        @endif
    </p>

    <div class="error-actions">
        <button onclick="setTimeout(() => location.reload(), 1000)" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            @if (app()->getLocale() == 'fr')
                Réessayer
            @else
                Try Again
            @endif
        </button>
        <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}" class="btn btn-outline">
            @if (app()->getLocale() == 'fr')
                Retour à l'accueil
            @else
                Back to Home
            @endif
        </a>
    </div>

    <div class="error-details">
        @if (app()->getLocale() == 'fr')
            Veuillez attendre 60 secondes avant de réessayer.
        @else
            Please wait 60 seconds before trying again.
        @endif
    </div>
@endsection
