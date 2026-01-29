<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Nom</th>
                <th>Slug</th>
                <th>Statut</th>
                <th>Position</th>
                <th>Créé</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->getName() }}</td>
                    <td>{{ $category->slug }}</td>

                    <td>
                        @if ($category->is_active)
                            <span class="badge bg-light-success text-success">Actif</span>
                        @else
                            <span class="badge bg-light-danger text-danger">Désactivé</span>
                        @endif
                    </td>

                    <td>{{ $category->position ?? '-' }}</td>

                    <td>{{ $category->created_at->format('Y-m-d') }}</td>

                    <td>
                        <a href="{{ route('backoffice.blog.categories.edit', $category) }}"
                            class="avtar avtar-xs btn-link-secondary me-2" title="Modifier" aria-label="Modifier">
                            <i class="ti ti-edit f-20"></i>
                        </a>

                        <form action="{{ route('backoffice.blog.categories.destroy', $category) }}" method="POST"
                            class="d-inline-block">
                            @csrf @method('DELETE')

                            <button type="submit" class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                onclick="return confirm('Supprimer cette catégorie ?')" title="Supprimer"
                                aria-label="Supprimer">
                                <i class="ti ti-trash f-20"></i>
                            </button>
                        </form>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Aucune catégorie trouvée.</td>
                </tr>
            @endforelse
        </tbody>

    </table>
</div>
