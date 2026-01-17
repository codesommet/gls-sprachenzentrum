<?php

return [
    'header' => [
        'title' => 'Inscription GLS',
        'subtitle' => 'Complétez les étapes pour envoyer votre demande',
    ],

    'progress' => [
        'steps' => [
            'step1' => 'Informations',
            'step2' => 'Centre GLS',
            'step3' => 'Groupe',
            'step4' => 'Préférences',
        ],
    ],

    'fields' => [
        'name' => [
            'label' => 'Nom complet',
            'placeholder' => 'Votre nom complet',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'email@example.com',
        ],
        'phone' => [
            'label' => 'Téléphone',
            'placeholder' => '+212 650-123456',
        ],
        'adresse' => [
            'label' => 'Adresse',
            'placeholder' => 'Votre adresse complète',
        ],
        'type_cours' => [
            'label' => 'Type de cours',
            'placeholder' => 'Choisissez un type',
        ],
        'type_cours_options' => [
            'presentiel' => 'Cours présentiel',
            'en_ligne' => 'Cours en ligne',
        ],
        'centre' => [
            'label' => 'Centre GLS préféré',
            'placeholder' => 'Sélectionner un centre',
        ],
        'group_id' => [
            'label' => 'Groupe',
            'placeholder' => 'Sélectionner un groupe',
        ],
        'niveau' => [
            'label' => 'Niveau d\'Allemand',
            'placeholder' => 'Sélectionner un niveau',
        ],
        'horaire_prefere' => [
            'label' => 'Horaire de cours',
            'placeholder' => 'Auto rempli',
        ],
        'date_start' => [
            'label' => 'À partir de...',
            'placeholder' => 'Sélectionner une date',
        ],
    ],

    'buttons' => [
        'prev' => 'Retour',
        'next' => 'Continuer',
    ],

    'messages' => [
        'success_title' => 'Merci !',
        'success_text' => 'Votre demande a bien été envoyée. Notre équipe vous contactera sous peu.',
    ],
];
