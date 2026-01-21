@extends('frontoffice.layouts.app')

@section('title', $quiz['title'] ?? 'Quiz')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/quiz/quiz.css') }}">

@section('content')

@php
  $quiz = $quiz ?? ['title' => 'Quiz', 'subtitle' => null, 'questions' => []];
@endphp

<section class="quiz-page">
  <div class="quiz-overlay" aria-hidden="true"></div>

  <div class="quiz-shell" data-quiz>
    <div class="quiz-topbar">
      <div class="quiz-brand">
        <span class="quiz-brand_dot"></span>
        <span class="quiz-brand_text">{{ $quiz['title'] ?? 'Quiz' }}</span>
      </div>

      <div class="quiz-meta">
        <span class="quiz-meta_label" data-quiz-counter></span>
      </div>
    </div>

    <div class="quiz-card" role="region" aria-label="Quiz">
      <div class="quiz-card_inner">

        {{-- Start screen --}}
        <div class="quiz-screen is-active" data-screen="start">

  {{-- GLS HEADER (à cacher après start) --}}
  <div class="quiz-hero" data-quiz-hero>
    <img
      src="{{ asset('build/images/logo/gls-noir.png') }}"
      alt="GLS Sprachenzentrum"
      class="quiz-hero_logo"
      loading="eager"
    />

    <h1 class="quiz-hero_title">
      Deutsch-Quiz {{ $quizLevel ?? 'A1' }}
    </h1>

    <p class="quiz-hero_subtitle">
      Am Ende bekommst du ein Feedback zu deinen Antworten.<br>
      <span>You will receive feedback on your answers.</span>
    </p>
  </div>

  @if(!empty($quiz['subtitle']))
    <p class="quiz-subtitle" data-quiz-subtitle>
      {{ $quiz['subtitle'] }}
    </p>
  @endif

  <div class="quiz-start_actions">
    <button type="button" class="quiz-btn quiz-btn_primary" data-quiz-start>
      Commencer
    </button>
  </div>
</div>

        {{-- Question screen --}}
        <div class="quiz-screen" data-screen="question">
          <div class="quiz-q_head">
            <div class="quiz-q_kicker" data-q-title></div>
            <div class="quiz-q_prompt" data-q-prompt></div>
          </div>

          <div class="quiz-q_body">
            <div class="quiz-q_text" data-q-question></div>

            {{-- ✅ Media block (image OR audio) --}}
            <div class="quiz-q_media" data-q-media hidden>
              <img data-q-image alt="" loading="lazy" hidden>
              <audio data-q-audio controls preload="none" hidden></audio>
            </div>

            <div class="quiz-answers" data-q-answers></div>
          </div>
        </div>

        {{-- Result screen --}}
<div class="quiz-screen" data-screen="result">

  <div class="quiz-result">
    <img
      src="{{ asset('build/images/logo/gls-noir.png') }}"
      alt="GLS Sprachenzentrum"
      class="quiz-result_logo"
      loading="lazy"
    />

    <h2 class="quiz-result_title">
      Herzlichen Glückwunsch!
    </h2>

    <p class="quiz-result_points">
      Du hast <strong data-result-correct>0</strong> von <strong data-result-total>0</strong> Fragen richtig beantwortet
      (<strong data-result-percent>0</strong>%).
    </p>

    <p class="quiz-result_level">
      <strong data-result-level>A1</strong> ist definitiv das richtige Level für dich!
      <span class="quiz-result_level_sub">You can get started any day now. Our courses are only a click away.</span>
    </p>

    <div class="quiz-result_actions">
      <button type="button" class="quiz-btn" data-quiz-restart>
        Refaire le quiz
      </button>

      {{-- Option: lien vers tes cours (change la route si tu veux) --}}
      <a href="{{ route('front.pricing') }}" class="quiz-btn quiz-btn_primary">
        Voir les cours
      </a>
    </div>
  </div>

</div>


      </div>

      <div class="quiz-nav">
        <button type="button" class="quiz-nav_btn" data-quiz-prev disabled>
          <span class="quiz-nav_arrow">←</span>
          <span class="quiz-nav_text">PREVIOUS</span>
        </button>

        <button type="button" class="quiz-nav_btn is-right" data-quiz-next disabled>
          <span class="quiz-nav_text">NEXT</span>
          <span class="quiz-nav_arrow">→</span>
        </button>
      </div>
    </div>

    <div class="quiz-progress" aria-label="Progression" data-quiz-progress></div>

    <form class="quiz-form" data-quiz-form hidden>
      @csrf
      <input type="hidden" name="quiz_id" value="{{ $quiz['id'] ?? '' }}">
      <input type="hidden" name="answers_json" value="" data-answers-json>
    </form>

  </div>
</section>

<script>
  window.__QUIZ__ = @json($quiz);
</script>

<script defer src="{{ asset('assets/js/quiz.js') }}"></script>

@endsection
