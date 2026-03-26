

<li class="pc-item pc-caption">
    <label>Gestion GLS</label>
    <i class="ph-duotone ph-folders"></i>
</li>
<!-- DASHBOARD -->
<li class="pc-item">
    <a href="{{ route('dashboard') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-gauge"></i></span>
        <span class="pc-mtext">Dashboard</span>
    </a>
</li>

<!-- SUIVI NIVEAU -->
<li class="pc-item">
    <a href="{{ route('backoffice.level_followups.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-chart-line-up"></i></span>
        <span class="pc-mtext">Suivi niveau</span>
    </a>
</li>

<!-- BLOG CATEGORIES -->
<li class="pc-item">
    <a href="{{ route('backoffice.blog.categories.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-bookmark-simple"></i></span>
        <span class="pc-mtext">Catégories Blog</span>
    </a>
</li>

<!-- BLOG POSTS -->
<li class="pc-item">
    <a href="{{ route('backoffice.blog.posts.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-newspaper"></i></span>
        <span class="pc-mtext">Articles Blog</span>
    </a>
</li>

<!-- SITES -->
<li class="pc-item">
    <a href="{{ route('backoffice.sites.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-buildings"></i></span>
        <span class="pc-mtext">Centres GLS</span>
    </a>
</li>

<!-- TEACHERS -->
<li class="pc-item">
    <a href="{{ route('backoffice.teachers.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-chalkboard-teacher"></i></span>
        <span class="pc-mtext">Enseignants</span>
    </a>
</li>

<!-- GROUPS -->
<li class="pc-item">
    <a href="{{ route('backoffice.groups.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-users-three"></i></span>
        <span class="pc-mtext">Groupes</span>
    </a>
</li>

<!-- CERTIFICATES -->
<li class="pc-item">
    <a href="{{ route('backoffice.certificates.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-certificate"></i></span>
        <span class="pc-mtext">Certificats</span>
    </a>
</li>

<!-- STUDIENKOLLEGS -->
<li class="pc-item">
    <a href="{{ route('backoffice.studienkollegs.index') }}" class="pc-link">
        <span class="pc-micon">
            <i class="ph-duotone ph-graduation-cap"></i>
        </span>
        <span class="pc-mtext">Studienkollegs</span>
    </a>
</li>

<li class="pc-item">
    <a href="{{ route('backoffice.quizzes.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-question"></i></span>
        <span class="pc-mtext">Quizzes (QCM)</span>
    </a>
</li>

{{-- LEADS (hidden – synced directly to Google Sheets)
<li class="pc-item">
    <a href="{{ route('backoffice.leads.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-address-book"></i></span>
        <span class="pc-mtext">Leads</span>
    </a>
</li>
--}}

<!-- USERS -->
<li class="pc-item">
    <a href="{{ route('backoffice.users.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-user-gear"></i></span>
        <span class="pc-mtext">Utilisateurs</span>
    </a>
</li>