@extends('errors.layout')

@section('title', '503 - Service Unavailable')

@section('content')
    <div class="error-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </div>

    <div class="error-code">503</div>

    <h1 class="error-title">
        @if (app()->getLocale() == 'fr')
            Maintenance en cours
        @else
            Under Maintenance
        @endif
    </h1>

    <p class="error-message">
        @if (app()->getLocale() == 'fr')
            Nous effectuons actuellement une maintenance programmée. Nous serons de retour très bientôt. Merci de votre
            patience !
        @else
            We're currently performing scheduled maintenance. We'll be back very soon. Thank you for your patience!
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
                Actualiser
            @else
                Refresh
            @endif
        </button>
    </div>

    <div class="error-details">
        @if (app()->getLocale() == 'fr')
            Heure estimée de retour : bientôt
        @else
            Estimated return time: soon
        @endif
    </div>
@endsection
