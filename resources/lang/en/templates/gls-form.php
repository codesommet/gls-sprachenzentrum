<?php

return [
    'header' => [
        'title' => 'GLS Registration',
        'subtitle' => 'Complete the steps to submit your request',
    ],

    'progress' => [
        'steps' => [
            'step1' => 'Information',
            'step2' => 'GLS Center',
            'step3' => 'Group',
            'step4' => 'Preferences',
        ],
    ],

    'fields' => [
        'name' => [
            'label' => 'Full Name',
            'placeholder' => 'Your full name',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'email@example.com',
        ],
        'phone' => [
            'label' => 'Phone',
            'placeholder' => '+212 650-123456',
        ],
        'adresse' => [
            'label' => 'Address',
            'placeholder' => 'Your full address',
        ],
        'type_cours' => [
            'label' => 'Course Type',
            'placeholder' => 'Choose a type',
        ],
        'type_cours_options' => [
            'presentiel' => 'In-Person Course',
            'en_ligne' => 'Online Course',
        ],
        'centre' => [
            'label' => 'Preferred GLS Center',
            'placeholder' => 'Select a center',
        ],
        'group_id' => [
            'label' => 'Group',
            'placeholder' => 'Select a group',
        ],
        'niveau' => [
            'label' => 'German Level',
            'placeholder' => 'Select a level',
        ],
        'horaire_prefere' => [
            'label' => 'Course Schedule',
            'placeholder' => 'Auto filled',
        ],
        'date_start' => [
            'label' => 'Start Date',
            'placeholder' => 'Select a date',
        ],
    ],

    'buttons' => [
        'prev' => 'Back',
        'next' => 'Continue',
    ],

    'messages' => [
        'success_title' => 'Thank you!',
        'success_text' => 'Your request has been sent successfully. Our team will contact you soon.',
    ],
];
