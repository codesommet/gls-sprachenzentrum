<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Nouvelle inscription GLS</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f9f9f9; padding:20px;">

    <div style="background:white; padding:25px; border-radius:12px; max-width:600px; margin:auto;">

        <h2 style="color:#111; margin-bottom:20px;">Nouvelle inscription GLS</h2>

        <p><strong>Nom :</strong> {{ $data['nom'] ?? '—' }}</p>
        <p><strong>Prénom :</strong> {{ $data['prenom'] ?? '—' }}</p>
        <p><strong>Email :</strong> {{ $data['email'] ?? '—' }}</p>
        <p><strong>Téléphone :</strong> {{ $data['phone'] ?? '—' }}</p>
        <p><strong>Adresse :</strong> {{ $data['adresse'] ?? '—' }}</p>

        <p><strong>Niveau :</strong> {{ $data['niveau'] ?? 'Non spécifié' }}</p>

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
            <p><strong>Centre choisi :</strong>
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
                <span style="color:#d9534f;">Aucun groupe sélectionné – merci de contacter notre équipe pour choisir un
                    groupe adapté.</span>
            @endif
        </p>

        {{-- HORAIRE --}}
        <p><strong>Horaire :</strong>
            @if (!empty($data['horaire_prefere']))
                {{ $data['horaire_prefere'] }}
            @else
                Non spécifié
            @endif
        </p>

        <p><strong>Date de début souhaitée :</strong>
            {{ $data['date_start'] ?? 'Non spécifiée' }}
        </p>

        <hr style="margin:25px 0;">

        <p style="font-size:14px; color:#666;">GLS Sprachenzentrum – Maroc</p>

    </div>

</body>

</html>
