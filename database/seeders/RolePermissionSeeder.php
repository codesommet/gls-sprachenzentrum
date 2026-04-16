<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |----------------------------------------------------------------------
        | Define all modules and their CRUD actions
        |----------------------------------------------------------------------
        */
        $modules = [
            'dashboard'        => ['view'],
            'sites'            => ['view', 'create', 'edit', 'delete'],
            'teachers'         => ['view', 'create', 'edit', 'delete'],
            'groups'           => ['view', 'create', 'edit', 'delete'],
            'certificates'     => ['view', 'create', 'edit', 'delete'],
            'studienkollegs'   => ['view', 'create', 'edit', 'delete'],
            'quizzes'          => ['view', 'create', 'edit', 'delete'],
            'blog_categories'  => ['view', 'create', 'edit', 'delete'],
            'blog_posts'       => ['view', 'create', 'edit', 'delete'],
            'leads'            => ['view', 'delete'],
            'lead_stats'       => ['view'],
            'applications'     => ['view', 'create', 'edit', 'delete'],
            'users'            => ['view', 'create', 'edit', 'delete'],
            'roles'            => ['view', 'create', 'edit', 'delete'],
            'payroll'          => ['view', 'create', 'edit', 'delete'],
            'presence'         => ['view', 'create', 'edit', 'delete'],
            'level_followups'  => ['view', 'create', 'edit', 'delete'],
            'weekly_reports'   => ['view', 'create', 'edit', 'delete'],
            'employees'        => ['view', 'create', 'edit', 'delete'],
            'schedules'        => ['view', 'create', 'edit', 'delete'],
        ];

        // Create all permissions
        $allPermissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permName = "{$module}.{$action}";
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
                $allPermissions[] = $permName;
            }
        }

        /*
        |----------------------------------------------------------------------
        | Super Admin — gets ALL permissions
        |----------------------------------------------------------------------
        */
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($allPermissions);

        /*
        |----------------------------------------------------------------------
        | Admin — everything except roles management
        |----------------------------------------------------------------------
        */
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminPermissions = collect($allPermissions)->reject(function ($perm) {
            return str_starts_with($perm, 'roles.');
        })->values()->toArray();
        $admin->syncPermissions($adminPermissions);

        /*
        |----------------------------------------------------------------------
        | Réception — pilotage, school modules (no delete), certificates, no admin
        |----------------------------------------------------------------------
        */
        $reception = Role::firstOrCreate(['name' => 'Réception', 'guard_name' => 'web']);
        $receptionPermissions = [
            // Dashboard
            'dashboard.view',
            // Pilotage
            'level_followups.view', 'level_followups.create', 'level_followups.edit',
            'weekly_reports.view', 'weekly_reports.create', 'weekly_reports.edit',
            // Enseignants (no delete)
            'teachers.view', 'teachers.create', 'teachers.edit',
            // Groupes (no delete)
            'groups.view', 'groups.create', 'groups.edit',
            // Certificats (no delete)
            'certificates.view', 'certificates.create', 'certificates.edit',
            // Studienkollegs (no delete)
            'studienkollegs.view', 'studienkollegs.create', 'studienkollegs.edit',
        ];
        $reception->syncPermissions($receptionPermissions);
    }
}
