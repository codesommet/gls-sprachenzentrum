@extends('backoffice.index')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">Quizzes (QCM)</h4>
        <a href="{{ route('backoffice.quizzes.create') }}" class="btn btn-primary">
            + New Quiz
        </a>
    </div>

    @include('backoffice.quizzes.table', ['quizzes' => $quizzes])
</div>
@endsection
