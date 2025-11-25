<?php
return [
    'strict_faculty_mapping' => true,

    'faculty_map' => [
        'FLA' => 'FLA',                 // semua kode FLA diarahkan ke FLA
        'FLA|FLA SURABAYA' => 'FLA',    // alias nama → tetap FLA
        // tambahkan alias lain kalau ada
    ],

    'faculty_name_map' => [
        'FLA SURABAYA' => 'FLA',        // fallback by name
    ],

    'program_map' => [
        // contoh jika perlu paksa program tertentu:
        // '1012' => ['faculty_code'=>'FLA', 'program_code'=>'ACC-MDN', 'program_name'=>'Accounting (Medan)'],
    ],
];
