@extends('errors.layout')

@section('title', '403 - Forbidden')

@section('content')
    <div class="error-icon" style="background: linear-gradient(135deg, #f97316, #ea580c);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
        </svg>
    </div>

    <div class="error-code">403</div>

    <h1 class="error-title">
        @if (app()->getLocale() == 'fr')
            Accès refusé
        @else
            Access Forbidden
        @endif
    </h1>

    <p class="error-message">
        @if (app()->getLocale() == 'fr')
            Vous n'avez pas la permission d'accéder à cette ressource. Si vous pensez qu'il s'agit d'une erreur, veuillez
            nous contacter.
        @else
            You don't have permission to access this resource. If you believe this is an error, please contact us.
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
