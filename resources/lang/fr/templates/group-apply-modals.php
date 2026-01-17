<?php

return [
    'header' => [
        'title' => 'Apply – Groupe GLS',
        'subtitle' => 'Votre demande sera envoyée avec le groupe sélectionné',
    ],

    'fields' => [
        'group' => [
            'label' => 'Groupe sélectionné',
            'placeholder' => 'Auto rempli',
        ],
        'schedule' => [
            'label' => 'Horaire',
            'placeholder' => 'Auto rempli',
        ],
        'level' => [
            'label' => 'Niveau',
            'placeholder' => 'Auto rempli',
        ],
        'full_name' => [
            'label' => 'Nom complet',
            'placeholder' => 'Votre nom complet',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'email@example.com',
        ],
        'phone' => [
            'label' => 'Téléphone',
            'placeholder' => '+212 6xx-xxxxxx',
        ],
        'address' => [
            'label' => 'Adresse',
            'placeholder' => 'Ville, rue...',
        ],
        'birthday' => [
            'label' => 'Date de naissance',
        ],
        'note' => [
            'label' => 'Note',
            'placeholder' => 'Message optionnel...',
        ],
    ],

    'buttons' => [
        'cancel' => 'Annuler',
        'submit' => 'Envoyer',
    ],

    'messages' => [
        'success_title' => 'Merci !',
        'success_text' => 'Votre demande a bien été envoyée. Notre équipe vous contactera sous peu.',
    ],
];
