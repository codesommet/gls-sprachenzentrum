@extends('errors.layout')

@section('title', '404 - Page Not Found')

@section('content')
    <div class="error-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>

    <div class="error-code">404</div>

    <h1 class="error-title">
        @if (app()->getLocale() == 'fr')
            Page introuvable
        @else
            Page Not Found
        @endif
    </h1>

    <p class="error-message">
        @if (app()->getLocale() == 'fr')
            Oups ! La page que vous recherchez n'existe pas ou a été déplacée.
        @else
            Oops! The page you're looking for doesn't exist or has been moved.
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

    <div class="error-details">
        <code>{{ request()->url() }}</code>
    </div>
@endsection
