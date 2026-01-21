@extends('backoffice.index')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">Edit Quiz</h4>
        <a href="{{ route('backoffice.quizzes.index') }}" class="btn btn-light">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('backoffice.quizzes.update', $quiz) }}" method="POST">
                @csrf
                @method('PUT')
                @include('backoffice.quizzes._form', ['quiz' => $quiz, 'levels' => $levels])
                <div class="mt-3">
                    <button class="btn btn-primary" type="submit">Update</button>
                    <a href="{{ route('backoffice.quizzes.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
