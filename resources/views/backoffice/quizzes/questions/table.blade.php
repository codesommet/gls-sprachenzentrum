<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:80px;">Diff</th>
                        <th>Question</th>
                        <th style="width:90px;">Points</th>
                        <th style="width:120px;">Active</th>
                        <th style="width:220px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $q)
                        <tr>
                            <td><span class="badge bg-info">{{ $q->difficulty }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($q->question_text, 120) }}</div>
                                <div class="text-muted small">
                                    Options: {{ $q->options->count() }} |
                                    Correct: {{ optional($q->options->firstWhere('is_correct', true))->option_text ?? '—' }}
                                </div>
                            </td>
                            <td>{{ $q->points }}</td>
                            <td>
                                @if($q->is_active)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('backoffice.quizzes.questions.edit', [$quiz, $q]) }}" class="btn btn-sm btn-outline-primary">
                                    Edit
                                </a>

                                <form action="{{ route('backoffice.quizzes.questions.destroy', [$quiz, $q]) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this question?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No questions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
