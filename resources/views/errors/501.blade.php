@extends('errors.layout')

@section('title', '501 - Not Implemented')

@section('content')
    <div class="error-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>
    </div>

    <div class="error-code">501</div>

    <h1 class="error-title">
        @if (app()->getLocale() == 'fr')
            Non implémenté
        @else
            Not Implemented
        @endif
    </h1>

    <p class="error-message">
        @if (app()->getLocale() == 'fr')
            Cette fonctionnalité n'est pas encore disponible. Notre équipe travaille dessus !
        @else
            This feature is not yet available. Our team is working on it!
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
        <a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}" class="btn btn-outline">
            @if (app()->getLocale() == 'fr')
                Nous contacter
            @else
                Contact Us
            @endif
        </a>
    </div>
@endsection
