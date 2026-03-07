<?php

return [
    'header' => [
        'title' => 'Bewerbung – GLS-Gruppe',
        'subtitle' => 'Ihre Anfrage wird mit der ausgewählten Gruppe gesendet',
    ],

    'fields' => [
        'group' => [
            'label' => 'Ausgewählte Gruppe',
            'placeholder' => 'Automatisch ausgefüllt',
        ],
        'schedule' => [
            'label' => 'Stundenplan',
            'placeholder' => 'Automatisch ausgefüllt',
        ],
        'level' => [
            'label' => 'Niveau',
            'placeholder' => 'Automatisch ausgefüllt',
        ],
        'full_name' => [
            'label' => 'Vollständiger Name',
            'placeholder' => 'Ihr vollständiger Name',
        ],
        'email' => [
            'label' => 'E-Mail',
            'placeholder' => 'email@example.com',
        ],
        'phone' => [
            'label' => 'Telefon',
            'placeholder' => '+212 6xx-xxxxxx',
        ],
        'address' => [
            'label' => 'Adresse',
            'placeholder' => 'Stadt, Straße...',
        ],
        'birthday' => [
            'label' => 'Geburtsdatum',
        ],
        'note' => [
            'label' => 'Anmerkung',
            'placeholder' => 'Optionale Nachricht...',
        ],
    ],

    'buttons' => [
        'cancel' => 'Abbrechen',
        'submit' => 'Senden',
    ],

    'messages' => [
        'success_title' => 'Vielen Dank!',
        'success_text' => 'Ihre Anfrage wurde erfolgreich gesendet. Unser Team wird Sie bald kontaktieren.',
    ],
];
