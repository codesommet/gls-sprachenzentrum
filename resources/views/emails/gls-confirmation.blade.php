<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Confirmation Inscription</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f7f7f7; padding:20px;">
    <div style="background:white; padding:25px; border-radius:12px; max-width:600px; margin:auto;">

        <h2 style="color:#111;">Merci pour votre inscription, {{ $data['nom'] }} {{ $data['prenom'] }} !</h2>

        <p>Nous avons bien reçu votre demande d'inscription au GLS Sprachenzentrum.</p>

        <p style="margin-top:20px;"><strong>Détails de votre demande :</strong></p>

        <p><strong>Nom :</strong> {{ $data['nom'] ?? '—' }}</p>
        <p><strong>Prénom :</strong> {{ $data['prenom'] ?? '—' }}</p>
        <p><strong>Email :</strong> {{ $data['email'] ?? '—' }}</p>
        <p><strong>Téléphone :</strong> {{ $data['phone'] ?? '—' }}</p>
        <p><strong>Adresse :</strong> {{ $data['adresse'] ?? '—' }}</p>

        <p><strong>Niveau choisi :</strong> {{ $data['niveau'] ?? 'Non spécifié' }}</p>

        <p><strong>Type de cours :</strong>
            @if (($data['type_cours'] ?? '') === 'presentiel')
                Cours en présentiel
            @elseif(($data['type_cours'] ?? '') === 'en_ligne')
                Cours en ligne
            @else
                Non spécifié
            @endif
        </p>

        {{-- CENTRE (Seulement si présentiel) --}}
        @if (($data['type_cours'] ?? '') === 'presentiel')
            <p><strong>Centre sélectionné :</strong>
                @if (!empty($centre))
                    {{ $centre->name }} – {{ $centre->city }}
                @else
                    Aucun centre sélectionné
                @endif
            </p>
        @endif

        {{-- GROUPE SELECTIONNÉ --}}
        <p><strong>Groupe choisi :</strong>
            @if (!empty($group))
                {{ $group->display_name ?? ($group->name ?? 'Groupe ' . $group->id) }}
            @else
                Aucun groupe sélectionné
            @endif
        </p>

        {{-- HORAIRE --}}
        @if (!empty($data['horaire_prefere']))
            <p><strong>Horaire :</strong> {{ $data['horaire_prefere'] }}</p>
        @endif

        @if (!empty($data['date_start']))
            <p><strong>Date de début :</strong> {{ $data['date_start'] }}</p>
        @endif

        <hr style="margin:25px 0;">

        <p>Notre équipe vous contactera très prochainement pour finaliser votre inscription.</p>

        <p style="color:#555; font-size:14px; margin-top:20px;">
            GLS Sprachenzentrum – Centres de langue allemande au Maroc
        </p>
    </div>
</body>

</html>
