<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Vérifié</th>
                <th>Création</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>

                    <td>{{ $user->name }}</td>

                    <td>{{ $user->email }}</td>

                    <td>
                        @if($user->email_verified_at)
                            <span class="badge bg-light-success">Oui</span>
                        @else
                            <span class="badge bg-light-danger">Non</span>
                        @endif
                    </td>

                    <td>{{ $user->created_at->format('Y-m-d') }}</td>

                    <td>

                        {{-- EDIT --}}
                        <a href="{{ route('backoffice.users.edit', $user->id) }}"
                            class="avtar avtar-xs btn-link-secondary me-2" title="Modifier" aria-label="Modifier">
                            <i class="ti ti-edit f-20"></i>
                        </a>

                        {{-- DELETE --}}
                        @if($user->id !== auth()->id())
                            <form action="{{ route('backoffice.users.destroy', $user->id) }}" method="POST"
                                class="d-inline-block">
                                @csrf @method('DELETE')
                                <button type="submit" class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                    onclick="return confirm('Supprimer cet utilisateur ?')" title="Supprimer"
                                    aria-label="Supprimer">
                                    <i class="ti ti-trash f-20"></i>
                                </button>
                            </form>
                        @endif

                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Aucun utilisateur trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
