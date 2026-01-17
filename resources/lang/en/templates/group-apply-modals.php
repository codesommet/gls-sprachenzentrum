<?php

return [
    'header' => [
        'title' => 'Apply – GLS Group',
        'subtitle' => 'Your request will be sent with the selected group',
    ],

    'fields' => [
        'group' => [
            'label' => 'Selected group',
            'placeholder' => 'Auto filled',
        ],
        'schedule' => [
            'label' => 'Schedule',
            'placeholder' => 'Auto filled',
        ],
        'level' => [
            'label' => 'Level',
            'placeholder' => 'Auto filled',
        ],
        'full_name' => [
            'label' => 'Full Name',
            'placeholder' => 'Your full name',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'email@example.com',
        ],
        'phone' => [
            'label' => 'Phone',
            'placeholder' => '+212 6xx-xxxxxx',
        ],
        'address' => [
            'label' => 'Address',
            'placeholder' => 'City, street...',
        ],
        'birthday' => [
            'label' => 'Date of Birth',
        ],
        'note' => [
            'label' => 'Note',
            'placeholder' => 'Optional message...',
        ],
    ],

    'buttons' => [
        'cancel' => 'Cancel',
        'submit' => 'Send',
    ],

    'messages' => [
        'success_title' => 'Thank you!',
        'success_text' => 'Your request has been sent successfully. Our team will contact you soon.',
    ],
];
