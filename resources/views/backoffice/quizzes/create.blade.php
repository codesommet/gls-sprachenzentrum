@extends('backoffice.index')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Créer Quiz</h4>
            <a href="{{ route('backoffice.quizzes.index') }}" class="btn btn-light">Retour</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('backoffice.quizzes.store') }}" method="POST">
                    @csrf
                    @include('backoffice.quizzes._form', ['quiz' => null, 'levels' => $levels])
                    <div class="mt-3">
                        <button class="btn btn-primary" type="submit">Enregistrer</button>
                        <a href="{{ route('backoffice.quizzes.index') }}" class="btn btn-light">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
