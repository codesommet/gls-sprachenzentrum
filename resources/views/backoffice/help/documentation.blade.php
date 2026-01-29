@extends('layouts.main')

@section('title', 'Centre d\'aide - Documentation')
@section('breadcrumb-item', 'Aide')
@section('breadcrumb-item-active', 'Documentation')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Centre d'aide - Documentation GLS</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Bienvenue dans le centre d'aide de GLS Sprachen Zentrum. Cette page contient
                        toutes les informations nécessaires pour utiliser le back-office.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===========================
    SECTION 1: DÉMARRAGE RAPIDE
    =========================== --}}
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="ph-duotone ph-rocket" style="font-size: 1.5rem; margin-right: 10px; color: #6C3FB3;"></i>
                        <h5 class="mb-0">Démarrage rapide</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Apprenez les bases pour commencer à utiliser le back-office de GLS :</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Dashboard :</strong> Consultez les statistiques globales et l'activité
                            récente</li>
                        <li class="mb-2"><strong>Navigation :</strong> Utilisez le menu latéral pour accéder aux
                            différents modules</li>
                        <li class="mb-2"><strong>Mon Compte :</strong> Gérez votre profil et changez votre mot de passe
                        </li>
                        <li class="mb-2"><strong>Déconnexion :</strong> Cliquez sur votre avatar en bas de la sidebar</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- CERTIFICATS --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="ph-duotone ph-certificate"
                            style="font-size: 1.5rem; margin-right: 10px; color: #FF6B35;"></i>
                        <h5 class="mb-0">Certificats</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Gérez les certificats d'examens de langue :</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Créer :</strong> Ajoutez un nouveau certificat avec les scores</li>
                        <li class="mb-2"><strong>Exporter PDF :</strong> Générez un PDF du certificat</li>
                        <li class="mb-2"><strong>Vérification publique :</strong> Les utilisateurs peuvent vérifier les
                            certificats via QR code</li>
                        <li class="mb-2"><strong>Accès :</strong> Menu → Certificats</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ===========================
    SECTION 2: GROUPES & ENSEIGNANTS
    =========================== --}}
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="ph-duotone ph-users-three"
                            style="font-size: 1.5rem; margin-right: 10px; color: #4ECDC4;"></i>
                        <h5 class="mb-0">Groupes</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Créez et gérez les groupes de cours :</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Créer un groupe :</strong> Définissez le niveau (A1-B2), les dates et la
                            période</li>
                        <li class="mb-2"><strong>Assignez des enseignants :</strong> Liez les professeurs au groupe</li>
                        <li class="mb-2"><strong>Gérez les candidatures :</strong> Approuvez ou rejetez les inscriptions
                        </li>
                        <li class="mb-2"><strong>Accès :</strong> Menu → Groupes</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ENSEIGNANTS --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="ph-duotone ph-chalkboard-teacher"
                            style="font-size: 1.5rem; margin-right: 10px; color: #FF6B9D;"></i>
                        <h5 class="mb-0">Enseignants</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Gérez votre équipe pédagogique :</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Ajouter un enseignant :</strong> Enregistrez les infos de contact et les
                            langues enseignées</li>
                        <li class="mb-2"><strong>Modifier :</strong> Mettez à jour les informations</li>
                        <li class="mb-2"><strong>Supprimer :</strong> Archivez les profils inactifs</li>
                        <li class="mb-2"><strong>Accès :</strong> Menu → Enseignants</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ===========================
    SECTION 3: BLOG & CONTENU
    =========================== --}}
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="ph-duotone ph-newspaper"
                            style="font-size: 1.5rem; margin-right: 10px; color: #FFE66D;"></i>
                        <h5 class="mb-0">Articles Blog</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Publiez du contenu sur votre blog :</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Créer un article :</strong> Rédigez du contenu avec images et vidéos</li>
                        <li class="mb-2"><strong>Catégories :</strong> Organisez les articles par sujets</li>
                        <li class="mb-2"><strong>Publication :</strong> Planifiez la publication ou publiez immédiatement
                        </li>
                        <li class="mb-2"><strong>Accès :</strong> Menu → Articles Blog</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- QUIZ/QCM --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="ph-duotone ph-question"
                            style="font-size: 1.5rem; margin-right: 10px; color: #95E1D3;"></i>
                        <h5 class="mb-0">Quizzes (QCM)</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Créez des quiz pour tester les connaissances :</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Niveaux :</strong> Associez les quiz aux niveaux de langue (A1-B2)</li>
                        <li class="mb-2"><strong>Questions :</strong> Ajoutez des questions avec plusieurs réponses</li>
                        <li class="mb-2"><strong>Résultats :</strong> Les utilisateurs voient leur score immédiatement
                        </li>
                        <li class="mb-2"><strong>Accès :</strong> Menu → Quizzes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ===========================
    SECTION 4: STUDIENKOLLEGS
    =========================== --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="ph-duotone ph-graduation-cap"
                            style="font-size: 1.5rem; margin-right: 10px; color: #A8E6CF;"></i>
                        <h5 class="mb-0">Studienkollegs</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Gérez les programmes de préparation universitaire :</p>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><strong>Créer un programme :</strong> Ajoutez des détails (nom,
                                    description, durée)</li>
                                <li class="mb-2"><strong>Images & Média :</strong> Ajoutez des photos et vidéos</li>
                                <li class="mb-2"><strong>Mise en avant :</strong> Marquez comme "featured" pour la page
                                    d'accueil</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><strong>Visibilité :</strong> Contrôlez si le programme est public ou
                                    privé</li>
                                <li class="mb-2"><strong>Filtres :</strong> Utilisateurs peuvent filtrer par ville,
                                    langue, niveau</li>
                                <li class="mb-2"><strong>Accès :</strong> Menu → Studienkollegs</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===========================
    SECTION 5: CENTRES GLS
    =========================== --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="ph-duotone ph-buildings"
                            style="font-size: 1.5rem; margin-right: 10px; color: #FFB7B2;"></i>
                        <h5 class="mb-0">Centres GLS</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Gérez les informations des centres de formation :</p>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><strong>Ajouter un centre :</strong> Adresse, contact, horaires</li>
                                <li class="mb-2"><strong>Informations :</strong> Numéro de téléphone, email, site web
                                </li>
                                <li class="mb-2"><strong>Géolocalisation :</strong> Coordonnées GPS pour la carte</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><strong>Photos :</strong> Galerie du centre</li>
                                <li class="mb-2"><strong>Programmes :</strong> Associez les groupes et studienkollegs
                                </li>
                                <li class="mb-2"><strong>Accès :</strong> Menu → Centres GLS</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===========================
    SECTION 6: SUPPORT & CONTACT
    =========================== --}}
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="card bg-brand-color-4" style="border: none;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="text-dark mb-2">Besoin d'aide supplémentaire ?</h5>
                            <p class="text-dark text-opacity-75 mb-0">Notre équipe de support est disponible pour répondre
                                à vos questions et résoudre vos problèmes.</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="https://Gls Team.support-hub.io/" class="btn btn-primary" target="_blank">
                                <i class="ph-duotone ph-phone me-2"></i>Accéder au Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
