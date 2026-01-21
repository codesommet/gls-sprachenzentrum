@extends('backoffice.index')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0">Questions — {{ $quiz->title }} ({{ $quiz->level }})</h4>
            <div class="text-muted">Manage QCM questions and options.</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('backoffice.quizzes.index') }}" class="btn btn-light">Back to quizzes</a>
            <a href="{{ route('backoffice.quizzes.questions.create', $quiz) }}" class="btn btn-primary">+ New Question</a>
        </div>
    </div>

    @include('backoffice.quizzes.questions.table', ['quiz' => $quiz, 'questions' => $questions])
</div>
@endsection
