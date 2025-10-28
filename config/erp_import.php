<?php

return [
    // Nama sheet & header kolom di file Excel (boleh disesuaikan)
    'sheet_name' => null, // null = sheet aktif pertama
    'columns' => [
        'faculty_code' => 'ACAD_GROUP',
        'faculty_name' => 'DESCR',       // nama fakultas
        'program_code' => 'Prodi_Code',  // kode prodi
        'program_name' => 'DESCR.1',     // nama prodi
        'course_id'    => 'CRSE_ID',
        'catalog_nbr'  => 'CATALOG_NBR',
        'course_name'  => 'DESCR.2',     // nama mata kuliah
    ],

    // Validasi kode prodi 5 digit (true = ketat, false = longgar)
    'strict_program_code_5digits' => true,

    // Strategy pemilihan nama fakultas kanonik jika 1 code punya >1 nama
    // options: 'shortest' | 'first' | 'longest'
    'faculty_canonical_strategy' => 'shortest',

    // Jika nama course berubah untuk (program_id, course_id) yang sama: 'keep_first' | 'update_to_latest' | 'error'
    'course_name_conflict' => 'keep_first',
];
