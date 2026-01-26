<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Image</th>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Statut</th>
                <th>À la une</th>
                <th>Créé</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($posts as $post)
                <tr>
                    <td>{{ $post->id }}</td>

                    <td>
                        @php
                            $media = $post->getFirstMedia('blog_images');
                        @endphp

                        <img src="{{ $media
                            ? route('media.custom', [
                                'id' => $media->id,
                                'filename' => $media->file_name,
                            ])
                            : asset('assets/images/placeholder.webp') }}"
                            alt="post-image" class="rounded" style="width: 55px; height: 45px; object-fit: cover;">


                    </td>

                    <td>{{ $post->title }}</td>

                    <td>
                        <span class="badge bg-light-primary text-primary">
                            {{ $post->category->name ?? '—' }}
                        </span>
                    </td>

                    <td>
                        @if ($post->status === 'published')
                            <span class="badge bg-light-success text-success">Publié</span>
                        @else
                            <span class="badge bg-light-warning text-warning">Brouillon</span>
                        @endif
                    </td>

                    <td>
                        @if ($post->featured)
                            <span class="badge bg-light-info text-info">Oui</span>
                        @else
                            <span class="badge bg-light-secondary text-muted">Non</span>
                        @endif
                    </td>

                    <td>{{ $post->created_at->format('Y-m-d') }}</td>

                    <td>
                        <a href="{{ route('backoffice.blog.posts.edit', $post) }}"
                            class="avtar avtar-xs btn-link-secondary me-2" title="Modifier">
                            <i class="ti ti-edit f-20"></i>
                        </a>

                        <form action="{{ route('backoffice.blog.posts.destroy', $post) }}" method="POST"
                            class="d-inline-block">
                            @csrf @method('DELETE')
                            <button class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                onclick="return confirm('Supprimer cet article ?')" title="Supprimer">
                                <i class="ti ti-trash f-20"></i>
                            </button>
                        </form>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Aucun article trouvé.</td>
                </tr>
            @endforelse
        </tbody>

    </table>
</div>
