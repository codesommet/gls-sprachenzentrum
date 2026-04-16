@php
    $dashboardActive = request()->routeIs('dashboard');
    $pilotageOpen = $dashboardActive || request()->routeIs('backoffice.level_followups.*') || request()->routeIs('backoffice.weekly_reports.*');
    $schoolOpen = request()->routeIs('backoffice.sites.*')
        || request()->routeIs('backoffice.teachers.*')
        || request()->routeIs('backoffice.groups.*')
        || request()->routeIs('backoffice.certificates.*')
        || request()->routeIs('backoffice.studienkollegs.*')
        || request()->routeIs('backoffice.quizzes.*');
    $admissionsOpen = request()->routeIs('backoffice.applications.*') || request()->routeIs('backoffice.leads.*');
    $payrollOpen = request()->routeIs('backoffice.payroll.*') && !request()->routeIs('backoffice.payroll.presence.*');
    $presenceOpen = request()->routeIs('backoffice.payroll.presence.*');
    $contentOpen = request()->routeIs('backoffice.blog.*');
    $rhOpen = request()->routeIs('backoffice.employees.*') || request()->routeIs('backoffice.schedules.*') || request()->routeIs('backoffice.planning.*');
    $adminOpen = request()->routeIs('backoffice.users.*') || request()->routeIs('backoffice.roles.*');
@endphp

<li class="pc-item pc-caption">
    <label>GLS Portal</label>
    <i class="ph-duotone ph-squares-four"></i>
</li>

@can('dashboard.view')
<li class="pc-item {{ $dashboardActive ? 'active' : '' }}">
    <a href="{{ route('dashboard') }}" class="pc-link {{ $dashboardActive ? 'active' : '' }}">
        <span class="pc-micon"><i class="ph-duotone ph-gauge"></i></span>
        <span class="pc-mtext">Dashboard</span>
    </a>
</li>
@endcan

@canany(['level_followups.view', 'weekly_reports.view'])
<li class="pc-item pc-hasmenu {{ $pilotageOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-chart-line-up"></i></span>
        <span class="pc-mtext">Pilotage</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        @can('level_followups.view')
        <li class="pc-item {{ request()->routeIs('backoffice.level_followups.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.level_followups.index') }}" class="pc-link {{ request()->routeIs('backoffice.level_followups.*') ? 'active' : '' }}">
                <span class="pc-mtext">Suivi niveau</span>
            </a>
        </li>
        @endcan
        @can('weekly_reports.view')
        <li class="pc-item {{ request()->routeIs('backoffice.weekly_reports.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.weekly_reports.index') }}" class="pc-link {{ request()->routeIs('backoffice.weekly_reports.*') ? 'active' : '' }}">
                <span class="pc-mtext">Rapport semaine</span>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany

@canany(['sites.view', 'teachers.view', 'groups.view', 'certificates.view', 'studienkollegs.view', 'quizzes.view'])
<li class="pc-item pc-hasmenu {{ $schoolOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-buildings"></i></span>
        <span class="pc-mtext">Gestion ecole</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        @can('sites.view')
        <li class="pc-item {{ request()->routeIs('backoffice.sites.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.sites.index') }}" class="pc-link {{ request()->routeIs('backoffice.sites.*') ? 'active' : '' }}">
                <span class="pc-mtext">Centres GLS</span>
            </a>
        </li>
        @endcan
        @can('teachers.view')
        <li class="pc-item {{ request()->routeIs('backoffice.teachers.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.teachers.index') }}" class="pc-link {{ request()->routeIs('backoffice.teachers.*') ? 'active' : '' }}">
                <span class="pc-mtext">Enseignants</span>
            </a>
        </li>
        @endcan
        @can('groups.view')
        <li class="pc-item {{ request()->routeIs('backoffice.groups.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.groups.index') }}" class="pc-link {{ request()->routeIs('backoffice.groups.*') ? 'active' : '' }}">
                <span class="pc-mtext">Groupes</span>
            </a>
        </li>
        @endcan
        @can('certificates.view')
        <li class="pc-item {{ request()->routeIs('backoffice.certificates.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.certificates.index') }}" class="pc-link {{ request()->routeIs('backoffice.certificates.*') ? 'active' : '' }}">
                <span class="pc-mtext">Certificats</span>
            </a>
        </li>
        @endcan
        @can('studienkollegs.view')
        <li class="pc-item {{ request()->routeIs('backoffice.studienkollegs.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.studienkollegs.index') }}" class="pc-link {{ request()->routeIs('backoffice.studienkollegs.*') ? 'active' : '' }}">
                <span class="pc-mtext">Studienkollegs</span>
            </a>
        </li>
        @endcan
        @can('quizzes.view')
        <li class="pc-item {{ request()->routeIs('backoffice.quizzes.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.quizzes.index') }}" class="pc-link {{ request()->routeIs('backoffice.quizzes.*') ? 'active' : '' }}">
                <span class="pc-mtext">Quizzes (QCM)</span>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany

@canany(['applications.view', 'leads.view', 'lead_stats.view'])
<li class="pc-item pc-hasmenu {{ $admissionsOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-address-book"></i></span>
        <span class="pc-mtext">Admissions & leads</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        @can('applications.view')
        <li class="pc-item {{ request()->routeIs('backoffice.applications.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.applications.index') }}" class="pc-link {{ request()->routeIs('backoffice.applications.*') ? 'active' : '' }}">
                <span class="pc-mtext">Applications</span>
            </a>
        </li>
        @endcan
        @can('leads.view')
        <li class="pc-item {{ request()->routeIs('backoffice.leads.index') ? 'active' : '' }}">
            <a href="{{ route('backoffice.leads.index') }}" class="pc-link {{ request()->routeIs('backoffice.leads.index') ? 'active' : '' }}">
                <span class="pc-mtext">Leads</span>
            </a>
        </li>
        @endcan
        @can('lead_stats.view')
        <li class="pc-item {{ request()->routeIs('backoffice.leads.stats') ? 'active' : '' }}">
            <a href="{{ route('backoffice.leads.stats') }}" class="pc-link {{ request()->routeIs('backoffice.leads.stats') ? 'active' : '' }}">
                <span class="pc-mtext">Statistiques Leads</span>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany

@can('payroll.view')
<li class="pc-item pc-hasmenu {{ $payrollOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-wallet"></i></span>
        <span class="pc-mtext">Suivi Paiement</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item {{ request()->routeIs('backoffice.payroll.dashboard') ? 'active' : '' }}">
            <a href="{{ route('backoffice.payroll.dashboard') }}" class="pc-link {{ request()->routeIs('backoffice.payroll.dashboard') ? 'active' : '' }}">
                <span class="pc-mtext">Tableau de bord</span>
            </a>
        </li>
        @can('payroll.create')
        <li class="pc-item {{ request()->routeIs('backoffice.payroll.import.create') ? 'active' : '' }}">
            <a href="{{ route('backoffice.payroll.import.create') }}" class="pc-link {{ request()->routeIs('backoffice.payroll.import.create') ? 'active' : '' }}">
                <span class="pc-mtext">Importer CRM</span>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcan

@can('presence.view')
<li class="pc-item pc-hasmenu {{ $presenceOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-chalkboard-teacher"></i></span>
        <span class="pc-mtext">Paiement Professeurs</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item {{ request()->routeIs('backoffice.payroll.presence.dashboard') ? 'active' : '' }}">
            <a href="{{ route('backoffice.payroll.presence.dashboard') }}" class="pc-link {{ request()->routeIs('backoffice.payroll.presence.dashboard') ? 'active' : '' }}">
                <span class="pc-mtext">Tableau de bord</span>
            </a>
        </li>
        @can('presence.create')
        <li class="pc-item {{ request()->routeIs('backoffice.payroll.presence.import.create') ? 'active' : '' }}">
            <a href="{{ route('backoffice.payroll.presence.import.create') }}" class="pc-link {{ request()->routeIs('backoffice.payroll.presence.import.create') ? 'active' : '' }}">
                <span class="pc-mtext">Importer Présence</span>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcan

@canany(['employees.view', 'schedules.view'])
<li class="pc-item pc-hasmenu {{ $rhOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-calendar-blank"></i></span>
        <span class="pc-mtext">RH / Planning</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        @can('employees.view')
        <li class="pc-item {{ request()->routeIs('backoffice.employees.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.employees.index') }}" class="pc-link {{ request()->routeIs('backoffice.employees.*') ? 'active' : '' }}">
                <span class="pc-mtext">Employés</span>
            </a>
        </li>
        @endcan
        @can('schedules.view')
        <li class="pc-item {{ request()->routeIs('backoffice.schedules.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.schedules.index') }}" class="pc-link {{ request()->routeIs('backoffice.schedules.*') ? 'active' : '' }}">
                <span class="pc-mtext">Planning</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.planning.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.planning.export-form') }}" class="pc-link {{ request()->routeIs('backoffice.planning.*') ? 'active' : '' }}">
                <span class="pc-mtext">Exportation PDF</span>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany

@canany(['blog_categories.view', 'blog_posts.view'])
<li class="pc-item pc-hasmenu {{ $contentOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-newspaper"></i></span>
        <span class="pc-mtext">Contenu</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        @can('blog_categories.view')
        <li class="pc-item {{ request()->routeIs('backoffice.blog.categories.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.blog.categories.index') }}" class="pc-link {{ request()->routeIs('backoffice.blog.categories.*') ? 'active' : '' }}">
                <span class="pc-mtext">Categories blog</span>
            </a>
        </li>
        @endcan
        @can('blog_posts.view')
        <li class="pc-item {{ request()->routeIs('backoffice.blog.posts.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.blog.posts.index') }}" class="pc-link {{ request()->routeIs('backoffice.blog.posts.*') ? 'active' : '' }}">
                <span class="pc-mtext">Articles blog</span>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany

@canany(['users.view', 'roles.view'])
<li class="pc-item pc-hasmenu {{ $adminOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-user-gear"></i></span>
        <span class="pc-mtext">Administration</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        @can('users.view')
        <li class="pc-item {{ request()->routeIs('backoffice.users.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.users.index') }}" class="pc-link {{ request()->routeIs('backoffice.users.*') ? 'active' : '' }}">
                <span class="pc-mtext">Utilisateurs</span>
            </a>
        </li>
        @endcan
        @can('roles.view')
        <li class="pc-item {{ request()->routeIs('backoffice.roles.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.roles.index') }}" class="pc-link {{ request()->routeIs('backoffice.roles.*') ? 'active' : '' }}">
                <span class="pc-mtext">Rôles & Permissions</span>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany
