<div class="row">

    {{-- ROLE NAME --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom du rôle</label>
        <input type="text" name="name"
               class="form-control"
               value="{{ old('name', $role->name ?? '') }}"
               placeholder="Nom du rôle"
               required>
    </div>

    <div class="col-12 mb-3">
        <hr>
        <h6 class="fw-bold mb-3">Matrice des permissions</h6>

        <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Tout cocher</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Tout décocher</button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 180px;">Module</th>
                        <th class="text-center" style="width: 100px;">Voir</th>
                        <th class="text-center" style="width: 100px;">Créer</th>
                        <th class="text-center" style="width: 100px;">Modifier</th>
                        <th class="text-center" style="width: 100px;">Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $moduleLabels = [
                            'dashboard'       => 'Dashboard',
                            'sites'           => 'Centres GLS',
                            'teachers'        => 'Enseignants',
                            'groups'          => 'Groupes',
                            'certificates'    => 'Certificats',
                            'studienkollegs'  => 'Studienkollegs',
                            'quizzes'         => 'Quizzes (QCM)',
                            'blog_categories' => 'Catégories Blog',
                            'blog_posts'      => 'Articles Blog',
                            'leads'           => 'Leads',
                            'lead_stats'      => 'Statistiques Leads',
                            'applications'    => 'Applications',
                            'users'           => 'Utilisateurs',
                            'roles'           => 'Rôles & Permissions',
                            'payroll'         => 'Suivi Paiement',
                            'presence'        => 'Paiement Professeurs',
                            'level_followups' => 'Suivi Niveau',
                            'weekly_reports'  => 'Rapport Semaine',
                            'employees'       => 'Employés',
                            'schedules'       => 'Planning',
                        ];
                        $actions = ['view', 'create', 'edit', 'delete'];
                        $actionLabels = [
                            'view'   => 'Voir',
                            'create' => 'Créer',
                            'edit'   => 'Modifier',
                            'delete' => 'Supprimer',
                        ];
                        $existingPermissions = isset($rolePermissions) ? $rolePermissions : [];
                    @endphp

                    @foreach($groupedPermissions as $module => $perms)
                        @php
                            $permNames = collect($perms)->pluck('action', 'name')->toArray();
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module)) }}</td>
                            @foreach($actions as $action)
                                <td class="text-center">
                                    @php
                                        $permName = "{$module}.{$action}";
                                        $hasAction = collect($perms)->where('action', $action)->isNotEmpty();
                                    @endphp
                                    @if($hasAction)
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input permission-checkbox"
                                                   type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permName }}"
                                                   id="perm_{{ $module }}_{{ $action }}"
                                                   {{ in_array($permName, old('permissions', $existingPermissions)) ? 'checked' : '' }}>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('selectAll')?.addEventListener('click', function () {
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
    });
    document.getElementById('deselectAll')?.addEventListener('click', function () {
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    });
});
</script>
