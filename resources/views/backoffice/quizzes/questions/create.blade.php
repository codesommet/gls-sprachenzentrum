@extends('backoffice.index')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">Create Question — {{ $quiz->level }}</h4>
        <a href="{{ route('backoffice.quizzes.questions.index', $quiz) }}" class="btn btn-light">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('backoffice.quizzes.questions.store', $quiz) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                @include('backoffice.quizzes.questions._form', ['quiz' => $quiz, 'question' => null])

                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit" name="action" value="save">
                        Save
                    </button>

                    <button class="btn btn-outline-primary" type="submit" name="action" value="save_next">
                        Save & Next
                    </button>

                    <a href="{{ route('backoffice.quizzes.questions.index', $quiz) }}" class="btn btn-light">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
