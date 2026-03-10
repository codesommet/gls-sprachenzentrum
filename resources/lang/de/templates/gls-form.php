<?php

return [
    'header' => [
        'title' => 'GLS-Anmeldung',
        'subtitle' => 'Führen Sie die Schritte aus, um Ihre Anfrage zu übermitteln',
    ],

    'progress' => [
        'steps' => [
            'step1' => 'Informationen',
            'step2' => 'GLS-Zentrum',
            'step3' => 'Gruppe',
            'step4' => 'Präferenzen',
        ],
    ],

    'fields' => [
        'nom' => [
            'label' => 'Nachname',
            'placeholder' => 'Ihr Nachname',
        ],
        'prenom' => [
            'label' => 'Vorname',
            'placeholder' => 'Ihr Vorname',
        ],
        'name' => [
            'label' => 'Vollständiger Name',
            'placeholder' => 'Ihr vollständiger Name',
        ],
        'email' => [
            'label' => 'E-Mail',
            'placeholder' => 'email@example.com',
        ],
        'phone' => [
            'label' => 'Telefon',
            'placeholder' => '+212 650-123456',
        ],
        'adresse' => [
            'label' => 'Adresse',
            'placeholder' => 'Ihre vollständige Adresse',
        ],
        'type_cours' => [
            'label' => 'Kurstyp',
            'placeholder' => 'Wählen Sie einen Typ',
        ],
        'type_cours_options' => [
            'presentiel' => 'Präsenzkurs',
            'en_ligne' => 'Online-Kurs',
        ],
        'centre' => [
            'label' => 'Bevorzugtes GLS-Zentrum',
            'placeholder' => 'Wählen Sie ein Zentrum',
        ],
        'group_id' => [
            'label' => 'Gruppe',
            'placeholder' => 'Wählen Sie eine Gruppe',
        ],
        'niveau' => [
            'label' => 'Deutschniveau',
            'placeholder' => 'Wählen Sie ein Niveau',
        ],
        'horaire_prefere' => [
            'label' => 'Kurszeitplan',
            'placeholder' => 'Automatisch ausgefüllt',
        ],
        'date_start' => [
            'label' => 'Startdatum',
            'placeholder' => 'Wählen Sie ein Datum',
        ],
        'accept_terms' => [
            'label' => 'Ich akzeptiere die',
            'link' => 'Allgemeinen Geschäftsbedingungen',
        ],
    ],

    'buttons' => [
        'prev' => 'Zurück',
        'next' => 'Weiter',
        'submit' => 'Absenden',
        'sending' => 'Wird gesendet...',
        'cancel' => 'Abbrechen',
        'back_home' => 'Zurück zur Startseite',
    ],

    'messages' => [
        'success_title' => 'Vielen Dank!',
        'success_text' => 'Ihre Anfrage wurde erfolgreich gesendet. Unser Team wird Sie bald kontaktieren.',
    ],

    'errors' => [
        'required_fields' => 'Bitte füllen Sie alle Pflichtfelder aus.',
        'duplicate' => 'Sie haben bereits eine Anfrage für diese Gruppe gestellt.',
        'server_error' => 'Serverfehler. Bitte versuchen Sie es erneut.',
        'connection_error' => 'Verbindungsfehler. Bitte versuchen Sie es erneut.',
        'generic' => 'Ein Fehler ist aufgetreten.',
        'session_expired' => 'Sitzung abgelaufen. Bitte laden Sie die Seite neu.',
        'check_fields' => 'Bitte überprüfen Sie die Formularfelder.',
    ],

    'js' => [
        'loading' => 'Wird geladen...',
        'error_loading' => 'Ladefehler',
        'select_level' => 'Niveau auswählen',
        'select_center' => 'Zentrum auswählen',
        'select_group' => 'Gruppe auswählen',
        'select_date' => 'Datum auswählen',
        'group_label' => 'Gruppe',
        'group_night_label' => 'Nachtgruppe',
    ],
];
