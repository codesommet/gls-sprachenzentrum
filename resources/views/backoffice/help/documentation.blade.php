@extends('layouts.main')

@section('title', 'Centre aide - Documentation')
@section('breadcrumb-item', 'Aide')
@section('breadcrumb-item-active', 'Documentation')

@section('css')
    <style>
        .doc-hero {
            border: 1px solid #e7ecf3;
            border-radius: 28px;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(4, 169, 245, 0.18), transparent 28%),
                linear-gradient(135deg, #ffffff 0%, #f7fbff 55%, #f4f8fd 100%);
            box-shadow: 0 24px 48px rgba(18, 38, 63, 0.08);
        }

        .doc-hero__body {
            padding: 32px;
        }

        .doc-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: #eaf5ff;
            color: #0b72c7;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .doc-hero__title {
            margin: 16px 0 10px;
            font-size: 2rem;
            line-height: 1.1;
            color: #233044;
            font-weight: 800;
        }

        .doc-hero__text {
            max-width: 820px;
            color: #5c6b82;
            font-size: 1rem;
            margin-bottom: 0;
        }

        .doc-metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-top: 24px;
        }

        .doc-metric {
            border: 1px solid #e8eef5;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.92);
            padding: 18px;
        }

        .doc-metric__value {
            font-size: 1.35rem;
            font-weight: 800;
            color: #233044;
        }

        .doc-metric__label {
            margin-top: 6px;
            color: #718198;
            font-size: 0.85rem;
        }

        .doc-section {
            margin-top: 24px;
        }

        .doc-card {
            border: 1px solid #e7ecf3;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(18, 38, 63, 0.05);
            height: 100%;
        }

        .doc-card__header {
            padding: 22px 24px 10px;
        }

        .doc-card__body {
            padding: 0 24px 24px;
        }

        .doc-card__icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 14px;
        }

        .doc-card__title {
            margin: 0;
            font-size: 1.12rem;
            font-weight: 800;
            color: #243042;
        }

        .doc-card__text {
            margin-top: 8px;
            margin-bottom: 0;
            color: #66768d;
        }

        .doc-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .doc-list li {
            position: relative;
            padding-left: 22px;
            margin-bottom: 12px;
            color: #475467;
        }

        .doc-list li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 9px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #04a9f5;
        }

        .doc-list strong {
            color: #243042;
        }

        .doc-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .doc-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .doc-chipline {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .doc-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 999px;
            background: #f4f7fb;
            color: #526177;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .doc-workflow {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .doc-step {
            border: 1px solid #e8eef5;
            border-radius: 20px;
            padding: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfe 100%);
        }

        .doc-step__num {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eaf5ff;
            color: #0b72c7;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .doc-step__title {
            margin: 0 0 8px;
            font-size: 1rem;
            font-weight: 800;
            color: #243042;
        }

        .doc-step__text {
            margin: 0;
            color: #66768d;
            font-size: 0.92rem;
        }

        .doc-note {
            border: 1px dashed #c8d7ea;
            border-radius: 20px;
            padding: 18px 20px;
            background: #f8fbff;
            color: #4f6077;
        }

        .doc-note strong {
            color: #233044;
        }

        .bg-doc-blue { background: #eaf5ff; color: #0b72c7; }
        .bg-doc-green { background: #e9fbf3; color: #0f8a58; }
        .bg-doc-orange { background: #fff2e8; color: #d66b1f; }
        .bg-doc-purple { background: #f1ebff; color: #6d44d8; }
        .bg-doc-pink { background: #ffeef5; color: #d84a84; }
        .bg-doc-yellow { background: #fff7df; color: #b98000; }

        @media (max-width: 1199.98px) {
            .doc-metrics,
            .doc-workflow,
            .doc-grid-3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .doc-hero__body {
                padding: 24px;
            }

            .doc-hero__title {
                font-size: 1.6rem;
            }

            .doc-grid-2,
            .doc-grid-3,
            .doc-metrics,
            .doc-workflow {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <section class="doc-hero">
                <div class="doc-hero__body">
                    <div class="doc-eyebrow">
                        <i class="ti ti-book-2"></i>
                        Guide operateur
                    </div>
                    <h1 class="doc-hero__title">Documentation d'utilisation du backoffice GLS</h1>
                    <p class="doc-hero__text">
                        Cette page sert de mode d'emploi interne pour l'equipe GLS. Elle explique comment naviguer dans le portail,
                        quelles operations faire dans chaque module et dans quel ordre traiter les taches quotidiennes.
                    </p>

                    <div class="doc-metrics">
                        <div class="doc-metric">
                            <div class="doc-metric__value">5</div>
                            <div class="doc-metric__label">zones du portail</div>
                        </div>
                        <div class="doc-metric">
                            <div class="doc-metric__value">1</div>
                            <div class="doc-metric__label">routine simple par jour</div>
                        </div>
                        <div class="doc-metric">
                            <div class="doc-metric__value">PDF</div>
                            <div class="doc-metric__label">exports disponibles</div>
                        </div>
                        <div class="doc-metric">
                            <div class="doc-metric__value">Equipe</div>
                            <div class="doc-metric__label">usage administratif</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-blue">
                            <i class="ti ti-compass"></i>
                        </div>
                        <h5 class="doc-card__title">Structure du portail</h5>
                    </div>
                    <div class="doc-card__body">
                        <div class="doc-chipline">
                            <span class="doc-chip"><i class="ti ti-layout-dashboard"></i> Dashboard</span>
                            <span class="doc-chip"><i class="ti ti-chart-line"></i> Pilotage</span>
                            <span class="doc-chip"><i class="ti ti-building"></i> Gestion ecole</span>
                            <span class="doc-chip"><i class="ti ti-address-book"></i> Admissions & leads</span>
                            <span class="doc-chip"><i class="ti ti-news"></i> Contenu</span>
                            <span class="doc-chip"><i class="ti ti-user-cog"></i> Administration</span>
                        </div>
                        <p class="doc-card__text mt-3">
                            Le menu lateral est organise par mission. Ouvrez toujours le module qui correspond a l'action
                            que vous voulez faire au lieu de chercher page par page.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-green">
                            <i class="ti ti-bolt"></i>
                        </div>
                        <h5 class="doc-card__title">Demarrage rapide</h5>
                    </div>
                    <div class="doc-card__body">
                        <div class="doc-workflow">
                            <div class="doc-step">
                                <div class="doc-step__num">1</div>
                                <h6 class="doc-step__title">Ouvrir le dashboard</h6>
                                <p class="doc-step__text">Verifier les compteurs et les elements a traiter en priorite.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">2</div>
                                <h6 class="doc-step__title">Traiter les leads</h6>
                                <p class="doc-step__text">Verifier consultations, inscriptions et applications recues.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">3</div>
                                <h6 class="doc-step__title">Suivre les groupes</h6>
                                <p class="doc-step__text">Controler groupes, enseignants et suivi niveau en cours.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">4</div>
                                <h6 class="doc-step__title">Mettre a jour le contenu</h6>
                                <p class="doc-step__text">Publier blogs, quiz, certificats ou documents si besoin.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-3">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue">
                        <i class="ti ti-chart-line"></i>
                    </div>
                    <h5 class="doc-card__title">Pilotage</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Dashboard :</strong> vue d'ensemble des activites et indicateurs.</li>
                        <li><strong>Suivi niveau :</strong> suivre la progression des groupes, ouvrir le detail, exporter le PDF groupe et marquer un suivi termine.</li>
                        <li><strong>Bon usage :</strong> traiter d'abord les cartes marquees en cours puis verifier les echeances.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange">
                        <i class="ti ti-school"></i>
                    </div>
                    <h5 class="doc-card__title">Gestion ecole</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Centres GLS :</strong> gerer les centres, coordonnees et informations de presentation.</li>
                        <li><strong>Enseignants :</strong> ajouter, modifier ou archiver les profils pedagogiques.</li>
                        <li><strong>Groupes :</strong> creer les groupes, choisir niveau, dates, centre et enseignant.</li>
                        <li><strong>Certificats :</strong> creer et exporter les certificats PDF.</li>
                        <li><strong>Studienkollegs / Quizzes :</strong> gerer l'offre academique et les tests.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-pink">
                        <i class="ti ti-user-heart"></i>
                    </div>
                    <h5 class="doc-card__title">Admissions & leads</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Applications :</strong> suivre les candidatures rattachees aux groupes.</li>
                        <li><strong>Leads :</strong> consulter les demandes entrantes et supprimer les doublons ou erreurs.</li>
                        <li><strong>Routine :</strong> verifier ce bloc chaque jour avant de passer aux autres modules.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-yellow">
                        <i class="ti ti-news"></i>
                    </div>
                    <h5 class="doc-card__title">Contenu</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Categories blog :</strong> structurer les sujets avant de publier.</li>
                        <li><strong>Articles blog :</strong> rediger, illustrer puis publier ou garder en brouillon.</li>
                        <li><strong>Conseil :</strong> definir la categorie avant l'article pour garder un backoffice propre.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple">
                        <i class="ti ti-shield-cog"></i>
                    </div>
                    <h5 class="doc-card__title">Administration</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Utilisateurs :</strong> gerer les acces au backoffice.</li>
                        <li><strong>Mon compte :</strong> mettre a jour son profil et son mot de passe.</li>
                        <li><strong>Regle simple :</strong> chaque utilisateur doit avoir un acces personnel, jamais partage.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green">
                        <i class="ti ti-life-buoy"></i>
                    </div>
                    <h5 class="doc-card__title">Support</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Centre d'aide :</strong> accessible depuis la sidebar.</li>
                        <li><strong>Blocage fonctionnel :</strong> noter le module, la page et l'action qui pose probleme.</li>
                        <li><strong>Support externe :</strong> utiliser le bouton de support en bas de page si necessaire.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue">
                        <i class="ti ti-list-check"></i>
                    </div>
                    <h5 class="doc-card__title">Routine quotidienne recommandee</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>1.</strong> Verifier le dashboard et les alertes du jour.</li>
                        <li><strong>2.</strong> Ouvrir `Admissions & leads` pour traiter les nouvelles demandes.</li>
                        <li><strong>3.</strong> Controler `Groupes` puis `Suivi niveau` pour les classes en cours.</li>
                        <li><strong>4.</strong> Generer les certificats ou PDF groupes si besoin.</li>
                        <li><strong>5.</strong> Mettre a jour le blog, les quiz ou les contenus publics si prevu.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange">
                        <i class="ti ti-alert-circle"></i>
                    </div>
                    <h5 class="doc-card__title">Bonnes pratiques</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Verifier avant suppression :</strong> toute suppression doit etre volontaire et confirmee.</li>
                        <li><strong>Nommer clairement :</strong> centres, groupes, articles et certificats doivent etre faciles a retrouver.</li>
                        <li><strong>Utiliser les filtres :</strong> ne pas travailler en liste complete si un filtre centre ou statut existe.</li>
                        <li><strong>Preferer la page detail :</strong> pour un suivi niveau, les notes restent dans la vue detail du groupe.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section mb-4">
        <div class="doc-note">
            <strong>Besoin d'aide supplementaire ?</strong>
            Utilisez cette page comme reference interne. Si une action n'est pas couverte ou si un module change,
            la documentation doit etre mise a jour en meme temps que l'interface.
        </div>
    </div>
@endsection
