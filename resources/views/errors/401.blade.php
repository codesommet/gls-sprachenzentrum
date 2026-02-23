@extends('errors.layout')

@section('title', '401 - Unauthorized')

@section('content')
    <div class="error-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
    </div>

    <div class="error-code">401</div>

    <h1 class="error-title">
        @if (app()->getLocale() == 'fr')
            Non autorisé
        @else
            Unauthorized
        @endif
    </h1>

    <p class="error-message">
        @if (app()->getLocale() == 'fr')
            Vous devez être connecté pour accéder à cette page. Veuillez vous connecter et réessayer.
        @else
            You must be logged in to access this page. Please log in and try again.
        @endif
    </p>

    <div class="error-actions">
        <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            @if (app()->getLocale() == 'fr')
                Retour à l'accueil
            @else
                Back to Home
            @endif
        </a>
    </div>
@endsection
