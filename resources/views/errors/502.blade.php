@extends('errors.layout')

@section('title', '502 - Bad Gateway')

@section('content')
    <div class="error-icon" style="background: linear-gradient(135deg, #ef4444, #b91c1c);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    </div>

    <div class="error-code">502</div>

    <h1 class="error-title">
        @if (app()->getLocale() == 'fr')
            Mauvaise passerelle
        @else
            Bad Gateway
        @endif
    </h1>

    <p class="error-message">
        @if (app()->getLocale() == 'fr')
            Le serveur a reçu une réponse invalide. Cela peut être temporaire, veuillez réessayer dans quelques instants.
        @else
            The server received an invalid response. This may be temporary, please try again in a few moments.
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
@endsection
