<?php

$jsonPath = env('GOOGLE_SHEETS_SERVICE_ACCOUNT_JSON', 'storage/app/google-service-account.json');

return [
    'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
    'service_account_json' => str_starts_with($jsonPath, '/') || str_contains($jsonPath, ':\\')
        ? $jsonPath                    // already absolute
        : base_path($jsonPath),        // resolve relative to project root
    'sheet_map' => json_decode(env('GOOGLE_SHEETS_SHEET_MAP', '{}'), true),
    'confirmed_sheet' => env('GOOGLE_SHEETS_CONFIRMED_SHEET', 'CONFIRMED'),

    // Static group ID → display name (must match the JS forms)
    'group_names' => [
        1  => 'Groupe 10:00 – 12:00',
        2  => 'Groupe 15:00 – 17:00',
        3  => 'Groupe 17:00 – 19:00',
        4  => 'Groupe 19:00 – 21:00',
        5  => 'Groupe 10:00 – 12:00',
        6  => 'Groupe 15:00 – 17:00',
        7  => 'Groupe 17:00 – 19:00',
        8  => 'Groupe 19:00 – 21:00',
        9  => 'Groupe 10:00 – 12:30',
        10 => 'Groupe 16:00 – 18:30',
        11 => 'Groupe 18:30 – 21:00',
        13 => 'Groupe 10:00 – 12:00',
        14 => 'Groupe 15:00 – 17:00',
        15 => 'Groupe 17:00 – 19:00',
        16 => 'Groupe 19:00 – 21:00',
        17 => 'Groupe 10:00 – 12:30',
        18 => 'Groupe 16:00 – 18:30',
        19 => 'Groupe 18:30 – 21:00',
        21 => 'Groupe 10:00 – 12:30',
        22 => 'Groupe 16:00 – 18:30',
        23 => 'Groupe 19:00 – 21:30',
        25 => 'Groupe Nuit 20:00 – 22:00',
    ],
];
