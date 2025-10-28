<?php

return [
    'version' => 'UPH-RPS-Rubric-2024',
    'indicators' => [
        [
            'no' => 1,
            'title' => 'Kesesuaian CPMK dan CPL 
Allignment CPMK and CPL',
            'criteria' => [
                [
                    'key' => '1.1',
                    'label' => '1. Kesesuaian CPMK dengan CPL (Terdapat Ranah: Pengetahuan, Ke...lan Umum, Keterampilan Khusus dan Sikap yang sesuai dengan KO)',
                    'scale' => [
                        5 => 'CPL sudah diambil dari KO, sudah ada kesesuaiaan dengan 3 ranah',
                        4 => 'CPL sudah diambil dari KO, sudah ada kesesuaiaan dengan 2 ranah',
                        3 => 'CPL sudah diambil dari KO, sudah ada kesesuaiaan dengan 1 ranah',
                        2 => 'CPL tidak ada kesesuaian dengan KO, namun masih ada keterkaitan dengan CPMK',
                        1 => 'CPL tidak ada kesesuain dengan KO dan tidak ada keterkaitan dengan CPMK',
                    ],
                    'notes_hint' => 'Mengacu pada Permendikbud 53 thn 2023, Bagian 2, pasal 7-10 Mengenai Standar Kompetensi Lulusan',
                ],
                [
                    'key' => '1.2',
                    'label' => '2. Kesesuaian kata kerja operasional CPMK dengan CPL',
                    'scale' => [
                        5 => 'Kata kerja operasional di CPMK sudah memiliki kesesuaian penurunan dengan kata kerja di CPL pada 3 ranah dengan tepat',
                        4 => 'Kata kerja operasional di CPMK sudah memiliki kesesuaian penurunan dengan kata kerja di CPL pada 2 ranah dengan tepat',
                        3 => 'Kata kerja operasional di CPMK sudah memiliki kesesuaian penurunan dengan kata kerja di CPL pada 1 ranah dengan tepat',
                        2 => 'Kata kerja operasional di CPMK belum memiliki kesesuaian penurunan dengan kata kerja di CPL',
                        1 => 'belum terdapat penurunan kata kerja operasional di CPL ke CPMK',
                    ],
                    'notes_hint' => 'Kesesuaian yang dimaksud adalah kata kerja operasional pada CPMK diturunkan dari CPL dan memiliki kesetaraan tingkat kognitif dan ranah yang ditentukan.',
                ],
                [
                    'key' => '1.3',
                    'label' => '3. Kesesuaian kata kerja operasional Sub-CPMK dengan CPMK',
                    'scale' => [
                        5 => 'Kata kerja operasional di Sub-CPMK sudah memiliki kesesuaian penurunan dengan kata kerja di CPMK pada 3 ranah dengan tepat',
                        4 => 'Kata kerja operasional di Sub-CPMK sudah memiliki kesesuaian penurunan dengan kata kerja di CPL pada 2 ranah dengan tepat',
                        3 => 'Kata kerja operasional di Sub-CPMK sudah memiliki kesesuaian penurunan dengan kata kerja di CPL pada 1 ranah dengan tepat',
                        2 => 'Kata kerja operasional di Sub-CPMK belum memiliki kesesuaian penurunan dengan kata kerja di CPL',
                        1 => 'belum terdapat penurunan kata kerja operasional di CPL ke Sub-CPMK',
                    ],
                    'notes_hint' => 'Kesesuaian yang dimaksud adalah penurunan CPMK ke sub-CPMK memiliki kesetaraan tingkat kognitif dan ranah yang ditentukan dan diikuti dengan kode penurunan yang jelas.',
                ],
            ],
        ],
        [
            'no' => 2,
            'title' => 'Tata cara penulisan CPMK 
Procedure for writing CPMK',
            'criteria' => [
                [
                    'key' => '2.1',
                    'label' => 'Penulisan CPMK harus memenuhi Kriteria ABCD. 
CPMK writing must meet the ABCD Criteria',
                    'scale' => [
                        5 => 'CPMK dituliskan dengan detail berdasarkan kaidah ABCD',
                        4 => 'Terdapat 3 kaidah penulisan dalam CPMK',
                        3 => 'Terdapat 2 kaidah penulisan dalam CPMK',
                        2 => 'Terdapat 1 kaidah penulisan dalam CPMK',
                        1 => 'Belum memenuhi kaidah penulisan CPMK',
                    ],
                    'notes_hint' => "KRITERIA ABCD                                                    Audience: target pembelajar
Behavior: kata kerja operasional yang dapat terukur sesuai dengan taksonomi Fink
Condition: Kondisi/keadaan bagaimana mahasiswa dapat mendemonstrasikan perilaku yang dikehendaki
Degree: Tingkatan keberhasilan mahasiswa dalam mencapai perilaku tersebut",
                ],
            ],
        ],
        [
            'no' => 3,
            'title' => 'Penilaian Pembelajaran 
Learning Assessment',
            'criteria' => [
                [
                    'key' => '3.1',
                    'label' => "1. Terdapat bobot (%) untuk setiap aktivitas penilaian
2. Kesesuaian bentuk penilaian untuk mengukur CPMK 
3. Terdapat deskripsi penilaian secara jelas
4. Terdapat waktu pelaksanaan assessment
5. Terdapat instrumen penilaian untuk penilaian yang berbentuk subjektif (Rubrik, Kuesioner, Skala, Lembar Observasi, dll)",
                    'scale' => [
                        5 => 'Terdapat 5 kriteria gradebook yang diisi secara lengkap',
                        4 => 'Terdapat 4 kriteria gradebook yang diisi secara lengkap',
                        3 => 'Hanya terdapat 3 kriteria gradebook yang diisi secara lengkap',
                        2 => 'Hanya terdapat 2 kriteria gradebook yang diisi secara lengkap',
                        1 => 'Hanya terdapat 1 kriteria gradebook yang diisi secara lengkap',
                    ],
                    'notes_hint' => 'Keterangan kriteria 5 : Jenis Penilaian yang telah dicantumkan pada mapping gradebook sesuai dengan kegiatan penilaian di dalam course planner',
                ],
            ],
        ],
        [
            'no' => 4,
            'title' => 'Integrasi Kegiatan Partisipatif dan Kolaboratif  dalam asesmen',
            'criteria' => [
                [
                    'key' => '4.1',
                    'label' => "1. Terdapat 50% bobot diambil dari kegiatan belajar kolaboratif dan partisipatif. Bobot yang dimaksud adalah nilai PAR dan atau PRO. 
Bobot kolaboratif dan partisipatif merupakan penilaian berbasis proyek atau kasus  dan bersifat authentic.                             
Bobot adalah prosentase penilaian PAR dan atau PRO.
2. Menerapkan metode pembelajaran berbasis proyek dan/atau kasus",
                    'scale' => [
                        5 => ' Terdapat min 50% bobot assessment untuk mengukur kegiatan belajar kolaboratif dan partisipatif seperti studi kasus dan proyek.',
                        4 => ' Terdapat 31%-40% bobot assessment untuk mengukur kegiatan belajar kolaboratif dan partisipatif seperti studi kasus dan proyek.',
                        3 => 'Terdapat 21%-30% bobot assessment untuk mengukur kegiatan belajar kolaboratif dan partisipatif seperti studi kasus dan proyek.',
                        2 => ' Terdapat 10%-20% bobot assessment untuk mengukur kegiatan belajar kolaboratif dan partisipatif seperti studi kasus dan proyek.',
                        1 => ' Tidak terdapat penilaian yang mengukur kegiatan belajar kolaboratif dan partisipatif seperti studi kasus dan proyek.  (<10%)',
                    ],
                    'notes_hint' => 'Mengacu pada tabel IKU 7 yaitu kelas yang kolaboratif dan partisipatif',
                ],
            ],
        ],
        [
            'no' => 5,
            'title' => 'Durasi 
Duration',
            'criteria' => [
                [
                    'key' => '5.1',
                    'label' => 'Terdapat estimasi waktu belajar dalam rencana kegiatan mengacu pada Permendikbud no 53 thn 2023 bag. 2 pasal 15 ayat 6 (1 sks = 45 jam / semester)',
                    'scale' => [
                        5 => 'Total durasi sesuai dengan jumlah SKS per minggu (sesuai aturan sks) dan terdapat pembagian waktunya berdasarkan aktifitas per sesi',
                        4 => 'Total durasi sesuai dengan jumlah SKS per minggu namun terdapat pembagian waktu disetiap sesi hanya sebagian aktifitas',
                        3 => 'Total durasi sesuai dengan jumlah SKS namun tidak terdapat pembagian waktu disetiap sesi',
                        2 => 'Total durasi tidak sesuai jumlah SKS namun terdapat pembagian waktu disetiap sesi',
                        1 => 'Tidak terdapat durasi dan pembagian waktu untuk setiap sesinya',
                    ],
                    'notes_hint' => 'Mengacu pada Permendikbud 53 thn 2023, Bagian 2, pasal 15 ayat 6 Mengenai Standar proses pembelajaran',
                ],
            ],
        ],
        [
            'no' => 6,
            'title' => 'Kelengkapan Course Planner 

Course Planner Completeness',
            'criteria' => [
                [
                    'key' => '6.1',
                    'label' => '1. Menggambarkan struktur mata kuliah (Modul, topik) yang jelas, ringkas dan terstruktur setiap sesi untuk mencapai sub CPMK (aktivitas sebelum kelas, saat kelas dan setelah kelas).  Stepping stone menggambarkan urutan yang jelas (pemaparan materi, praktik, refleksi, penilaian) sesi demi sesi dari awal sampai akhir semester. (Alur kegiatan yang mengarah pada integrase PAR dan PRO)',
                    'scale' => [
                        5 => 'Memenuhi semua (5) kriteria umum kelengkapan course Planner',
                        4 => 'Memenuhi 4 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        3 => 'Memenuhi 3 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        2 => 'Memenuhi 2 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        1 => 'Memenuhi 1 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                    ],
                    'notes_hint' => '-',
                ],
                [
                    'key' => '6.2',
                    'label' => '2. Terdapat Bentuk penilaian setiap sesi (Indikator dan bentuk penilaian)',
                    'scale' => [5 => 'Memenuhi semua (5) kriteria umum kelengkapan course Planner',
                        4 => 'Memenuhi 4 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        3 => 'Memenuhi 3 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        2 => 'Memenuhi 2 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        1 => 'Memenuhi 1 kriteria dari total 5 kriteria umum kelengkapan course Planner',],
                    'notes_hint' => '',
                ],
                [
                    'key' => '6.3',
                    'label' => '3. Terdapat kegiatan belajar yang diikuti oleh assessment dan refleksi',
                    'scale' => [5 => 'Memenuhi semua (5) kriteria umum kelengkapan course Planner',
                        4 => 'Memenuhi 4 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        3 => 'Memenuhi 3 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        2 => 'Memenuhi 2 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        1 => 'Memenuhi 1 kriteria dari total 5 kriteria umum kelengkapan course Planner',],
                    'notes_hint' => '',
                ],
                [
                    'key' => '6.4',
                    'label' => '4. Terdapat referensi yang digunakan di setiap sesi (Daftar Pustaka)',
                    'scale' => [5 => 'Memenuhi semua (5) kriteria umum kelengkapan course Planner',
                        4 => 'Memenuhi 4 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        3 => 'Memenuhi 3 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        2 => 'Memenuhi 2 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        1 => 'Memenuhi 1 kriteria dari total 5 kriteria umum kelengkapan course Planner',],
                    'notes_hint' => '',
                ],
                [
                    'key' => '6.5',
                    'label' => '5. Terdapat detail pembagian bobot penilaian di rencana kegiatan',
                    'scale' => [5 => 'Memenuhi semua (5) kriteria umum kelengkapan course Planner',
                        4 => 'Memenuhi 4 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        3 => 'Memenuhi 3 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        2 => 'Memenuhi 2 kriteria dari total 5 kriteria umum kelengkapan course Planner',
                        1 => 'Memenuhi 1 kriteria dari total 5 kriteria umum kelengkapan course Planner',],
                    'notes_hint' => '',
                ],
            ],
        ],
        [
            'no' => 7,
            'title' => 'Metode pembelajaran (Student-centered Learning)',
            'criteria' => [
                [
                    'key' => '7.1',
                    'label' => '1. Metode pembelajaran dirancang bervariasi memenuhi kriteria "guided/instructor-led" dan "independent/self-paced"',
                    'scale' => [
                        5 => 'Memenuhi 5 kriteria ',
                        4 => 'Memenuhi 4 kriteria dari total 5 kriteria',
                        3 => 'Memenuhi 3 kriteria dari total 5 kriteria',
                        2 => 'Memenuhi 2 kriteria dari total 5 kriteria',
                        1 => 'Memenuhi 1 kriteria dari total 5 kriteria',
                    ],
                    'notes_hint' => '',
                ],
                [
                    'key' => '7.2',
                    'label' => '2. Modalitas pembelajaran mengkombinasikan sesi tatap muka dan daring (synchronous dan asynchronous)',
                    'scale' => [5 => 'Memenuhi 5 kriteria ',
                        4 => 'Memenuhi 4 kriteria dari total 5 kriteria',
                        3 => 'Memenuhi 3 kriteria dari total 5 kriteria',
                        2 => 'Memenuhi 2 kriteria dari total 5 kriteria',
                        1 => 'Memenuhi 1 kriteria dari total 5 kriteria',],
                    'notes_hint' => '',
                ],
                [
                    'key' => '7.3',
                    'label' => '3. Metode pembelajaran dirancang untuk kelas kolaboratif (team based project)',
                    'scale' => [5 => 'Memenuhi 5 kriteria ',
                        4 => 'Memenuhi 4 kriteria dari total 5 kriteria',
                        3 => 'Memenuhi 3 kriteria dari total 5 kriteria',
                        2 => 'Memenuhi 2 kriteria dari total 5 kriteria',
                        1 => 'Memenuhi 1 kriteria dari total 5 kriteria',],
                    'notes_hint' => '',
                ],
                [
                    'key' => '7.4',
                    'label' => '4. Metode pembelajaran dirancang untuk kelas partisipatif (case-based method, problem based, etc salah satu dari 10 SCL)',
                    'scale' => [5 => 'Memenuhi 5 kriteria ',
                        4 => 'Memenuhi 4 kriteria dari total 5 kriteria',
                        3 => 'Memenuhi 3 kriteria dari total 5 kriteria',
                        2 => 'Memenuhi 2 kriteria dari total 5 kriteria',
                        1 => 'Memenuhi 1 kriteria dari total 5 kriteria',],
                    'notes_hint' => '',
                ],
                [
                    'key' => '7.5',
                    'label' => '5. Terdapat penugasan kelompok dan individu',
                    'scale' => [5 => 'Memenuhi 5 kriteria ',
                        4 => 'Memenuhi 4 kriteria dari total 5 kriteria',
                        3 => 'Memenuhi 3 kriteria dari total 5 kriteria',
                        2 => 'Memenuhi 2 kriteria dari total 5 kriteria',
                        1 => 'Memenuhi 1 kriteria dari total 5 kriteria',],
                    'notes_hint' => '',
                ],
            ],
        ],
        [
            'no' => 8,
            'title' => 'Karakteristik Course Planner Blended Learning  
Characteristics of Course Planner Blended Learning',
            'criteria' => [
                [
                    'key' => '8.1',
                    'label' => '1. Menyediakan peluang untuk kegiatan refleksi di kelas (in-class), antara sesi (inter-class), dan pasca sesi (post-class).',
                    'scale' => [5 => 'Sudah memenuhi Karakteristik Course Planner Blended Learning baik secara proporsi kegiatan maupun fleksibilitas dengan lengkap ',
                        4 => 'Sudah memenuhi  kriteria Karakteristik Course Planner Blended Learning baik secara proporsi kegiatan maupun fleksibilitas namun belum lengkap dan detail',
                        3 => 'Sudah memenuhi salah satu kriteria Karakteristik Course Planner Blended Learning secara proporsi kegiatan atau fleksibilitas dengan lengkap',
                        2 => 'Sudah memenuhi salah satu kriteria Karakteristik Course Planner Blended Learning secara proporsi kegiatan atau fleksibilitas namun belum lengkap dan detail',
                        1 => 'Belum memenuhi  kriteria Karakteristik Course Planner Blended Learning ',],
                    'notes_hint' => '',
                ],
                [
                    'key' => '8.2',
                    'label' => '2. Mengintegrasikan teknologi digital untuk mendukung pembelajaran campuran (blended learning)',
                    'scale' => [5 => 'Sudah memenuhi Karakteristik Course Planner Blended Learning baik secara proporsi kegiatan maupun fleksibilitas dengan lengkap ',
                        4 => 'Sudah memenuhi  kriteria Karakteristik Course Planner Blended Learning baik secara proporsi kegiatan maupun fleksibilitas namun belum lengkap dan detail',
                        3 => 'Sudah memenuhi salah satu kriteria Karakteristik Course Planner Blended Learning secara proporsi kegiatan atau fleksibilitas dengan lengkap',
                        2 => 'Sudah memenuhi salah satu kriteria Karakteristik Course Planner Blended Learning secara proporsi kegiatan atau fleksibilitas namun belum lengkap dan detail',
                        1 => 'Belum memenuhi  kriteria Karakteristik Course Planner Blended Learning ',],
                    'notes_hint' => '',
                ],
            ],
        ],
        [
            'no' => 9,
            'title' => 'Integrasi penelitian dan atau pkm sebagai salah satu metode pembelajaran 
Integration of Research and/or Community Service (PkM) as an active learning method',
            'criteria' => [
                [
                    'key' => '9.1',
                    'label' => '1. Terdapat integrasi penelitian dosen/mahasiswa dalam kegiatan pembelajaran',
                    'scale' => [],
                    'notes_hint' => '',
                ],
                [
                    'key' => '9.2',
                    'label' => '2. Terdapat integrasi PkM dosen/mahasiswa dalam kegiatan pembelajaran',
                    'scale' => [],
                    'notes_hint' => '',
                ],
            ],
        ],
    ],
];
