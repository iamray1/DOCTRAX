<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Submit Document Types
    |--------------------------------------------------------------------------
    |
    | These are the fallback options shown for a destination office when there
    | is no office-specific override yet. Keep "Others" out of this list; the
    | submit form appends it automatically for every office.
    |
    */
    'default' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Office-Specific Overrides
    |--------------------------------------------------------------------------
    |
    | Key each office list by the office code in uppercase.
    | Example:
    | 'SGOD' => ['Travel Order', 'Office Memorandum'],
    |
    */
    'by_office_code' => [
    ],
];
