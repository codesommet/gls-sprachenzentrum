@php
    $dashboardActive = request()->routeIs('dashboard');
    $pilotageOpen = $dashboardActive || request()->routeIs('backoffice.level_followups.*');
    $schoolOpen = request()->routeIs('backoffice.sites.*')
        || request()->routeIs('backoffice.teachers.*')
        || request()->routeIs('backoffice.groups.*')
        || request()->routeIs('backoffice.certificates.*')
        || request()->routeIs('backoffice.studienkollegs.*')
        || request()->routeIs('backoffice.quizzes.*');
    $admissionsOpen = request()->routeIs('backoffice.applications.*') || request()->routeIs('backoffice.leads.*');
    $contentOpen = request()->routeIs('backoffice.blog.*');
    $adminOpen = request()->routeIs('backoffice.users.*');
@endphp

<li class="pc-item pc-caption">
    <label>GLS Portal</label>
    <i class="ph-duotone ph-squares-four"></i>
</li>

<li class="pc-item {{ $dashboardActive ? 'active' : '' }}">
    <a href="{{ route('dashboard') }}" class="pc-link {{ $dashboardActive ? 'active' : '' }}">
        <span class="pc-micon"><i class="ph-duotone ph-gauge"></i></span>
        <span class="pc-mtext">Dashboard</span>
    </a>
</li>

<li class="pc-item pc-hasmenu {{ $pilotageOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-chart-line-up"></i></span>
        <span class="pc-mtext">Pilotage</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item {{ request()->routeIs('backoffice.level_followups.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.level_followups.index') }}" class="pc-link {{ request()->routeIs('backoffice.level_followups.*') ? 'active' : '' }}">
                <span class="pc-mtext">Suivi niveau</span>
            </a>
        </li>
    </ul>
</li>

<li class="pc-item pc-hasmenu {{ $schoolOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-buildings"></i></span>
        <span class="pc-mtext">Gestion ecole</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item {{ request()->routeIs('backoffice.sites.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.sites.index') }}" class="pc-link {{ request()->routeIs('backoffice.sites.*') ? 'active' : '' }}">
                <span class="pc-mtext">Centres GLS</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.teachers.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.teachers.index') }}" class="pc-link {{ request()->routeIs('backoffice.teachers.*') ? 'active' : '' }}">
                <span class="pc-mtext">Enseignants</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.groups.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.groups.index') }}" class="pc-link {{ request()->routeIs('backoffice.groups.*') ? 'active' : '' }}">
                <span class="pc-mtext">Groupes</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.certificates.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.certificates.index') }}" class="pc-link {{ request()->routeIs('backoffice.certificates.*') ? 'active' : '' }}">
                <span class="pc-mtext">Certificats</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.studienkollegs.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.studienkollegs.index') }}" class="pc-link {{ request()->routeIs('backoffice.studienkollegs.*') ? 'active' : '' }}">
                <span class="pc-mtext">Studienkollegs</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.quizzes.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.quizzes.index') }}" class="pc-link {{ request()->routeIs('backoffice.quizzes.*') ? 'active' : '' }}">
                <span class="pc-mtext">Quizzes (QCM)</span>
            </a>
        </li>
    </ul>
</li>

<li class="pc-item pc-hasmenu {{ $admissionsOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-address-book"></i></span>
        <span class="pc-mtext">Admissions & leads</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item {{ request()->routeIs('backoffice.applications.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.applications.index') }}" class="pc-link {{ request()->routeIs('backoffice.applications.*') ? 'active' : '' }}">
                <span class="pc-mtext">Applications</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.leads.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.leads.index') }}" class="pc-link {{ request()->routeIs('backoffice.leads.*') ? 'active' : '' }}">
                <span class="pc-mtext">Leads</span>
            </a>
        </li>
    </ul>
</li>

<li class="pc-item pc-hasmenu {{ $contentOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-newspaper"></i></span>
        <span class="pc-mtext">Contenu</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item {{ request()->routeIs('backoffice.blog.categories.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.blog.categories.index') }}" class="pc-link {{ request()->routeIs('backoffice.blog.categories.*') ? 'active' : '' }}">
                <span class="pc-mtext">Categories blog</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.blog.posts.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.blog.posts.index') }}" class="pc-link {{ request()->routeIs('backoffice.blog.posts.*') ? 'active' : '' }}">
                <span class="pc-mtext">Articles blog</span>
            </a>
        </li>
    </ul>
</li>

<li class="pc-item pc-hasmenu {{ $adminOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-user-gear"></i></span>
        <span class="pc-mtext">Administration</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item {{ request()->routeIs('backoffice.users.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.users.index') }}" class="pc-link {{ request()->routeIs('backoffice.users.*') ? 'active' : '' }}">
                <span class="pc-mtext">Utilisateurs</span>
            </a>
        </li>
    </ul>
</li>
