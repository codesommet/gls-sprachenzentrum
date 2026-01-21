<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:90px;">Level</th>
                        <th>Title</th>
                        <th style="width:160px;">Questions</th>
                        <th style="width:120px;">Active</th>
                        <th style="width:260px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quizzes as $quiz)
                        <tr>
                            <td><strong>{{ $quiz->level }}</strong></td>
                            <td>{{ $quiz->title }}</td>
                            <td>
                                <a href="{{ route('backoffice.quizzes.questions.index', $quiz) }}" class="btn btn-sm btn-outline-secondary">
                                    Manage Questions
                                </a>
                            </td>
                            <td>
                                @if($quiz->is_active)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('backoffice.quizzes.edit', $quiz) }}" class="btn btn-sm btn-outline-primary">
                                    Edit
                                </a>

                                <form action="{{ route('backoffice.quizzes.destroy', $quiz) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Delete this quiz?')">
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
                                No quizzes found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
