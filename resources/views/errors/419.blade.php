@extends('errors.layout')

@section('title', '419 - Session Expired')

@section('content')
    <div class="error-icon" style="background: linear-gradient(135deg, #ec4899, #db2777);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>

    <div class="error-code">419</div>

    <h1 class="error-title">
        @if (app()->getLocale() == 'fr')
            Session expirée
        @else
            Session Expired
        @endif
    </h1>

    <p class="error-message">
        @if (app()->getLocale() == 'fr')
            Votre session a expiré. Veuillez actualiser la page et réessayer.
        @else
            Your session has expired. Please refresh the page and try again.
        @endif
    </p>

    <div class="error-actions">
        <button onclick="location.reload()" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            @if (app()->getLocale() == 'fr')
                Actualiser la page
            @else
                Refresh Page
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
@endsection
