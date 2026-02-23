{{-- resources/views/frontoffice/gls-success.blade.php --}}
@extends('frontoffice.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/gls-form-page.css') }}">
    <style>
        .success-page {
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-card {
            background: #fffee8;
            border-radius: 28px;
            padding: 60px;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15);
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid rgba(33, 30, 29, 0.08);
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon svg {
            width: 50px;
            height: 50px;
            color: white;
        }

        .success-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
        }

        .success-message {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .success-details {
            background: rgba(16, 185, 129, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .success-details p {
            margin: 0;
            color: #065f46;
            font-weight: 500;
        }

        .success-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .success-actions .button {
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .success-actions .button-primary {
            background: linear-gradient(135deg, #211e1d, #3e3832);
            color: white;
        }

        .success-actions .button-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(33, 30, 29, 0.3);
        }

        .success-actions .button-outline {
            background: transparent;
            border: 2px solid #d1d5db;
            color: #374151;
        }

        .success-actions .button-outline:hover {
            border-color: #9ca3af;
            background: #f9fafb;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            0% {
                transform: translateY(20px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        .delay-4 {
            animation-delay: 0.4s;
        }
    </style>
@endpush

@section('content')
    @php
        $tr = function (string $key, string $fallback) {
            $val = __($key);
            return $val === $key ? $fallback : $val;
        };
    @endphp

    <main class="success-page py-5">
        <div class="container">
            <div class="success-card">

                <div class="success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h1 class="success-title fade-in-up delay-1">
                    {{ $tr('gls-success.title', 'Felicitations !') }}
                </h1>

                <p class="success-message fade-in-up delay-2">
                    {{ $tr('gls-success.message', 'Votre inscription a ete enregistree avec succes. Un email de confirmation vous a ete envoye.') }}
                </p>

                <div class="success-details fade-in-up delay-3">
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor"
                            style="display: inline; vertical-align: middle; margin-right: 8px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ $tr('gls-success.email_sent', 'Email de confirmation envoye !') }}
                    </p>
                </div>

                <div class="success-actions fade-in-up delay-4">
                    <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}" class="button button-primary">
                        {{ $tr('gls-success.back_home', "Retour a l'accueil") }}
                    </a>
                    <a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}" class="button button-outline">
                        {{ $tr('gls-success.contact_us', 'Nous contacter') }}
                    </a>
                </div>

            </div>
        </div>
    </main>
@endsection
