@extends('errors.layout')

@section('title', '500 - Server Error')

@section('content')
    <div class="error-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    </div>

    <div class="error-code">500</div>

    <h1 class="error-title">
        @if (app()->getLocale() == 'fr')
            Erreur serveur
        @else
            Server Error
        @endif
    </h1>

    <p class="error-message">
        @if (app()->getLocale() == 'fr')
            Quelque chose s'est mal passé de notre côté. Notre équipe technique a été notifiée et travaille à résoudre le
            problème.
        @else
            Something went wrong on our end. Our technical team has been notified and is working to fix the issue.
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
        <button onclick="location.reload()" class="btn btn-outline">
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
    </div>
@endsection
