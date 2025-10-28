<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Faculties
        DB::table('faculties')->insert([
            ['code' => 'FIK', 'name' => 'Fakultas Ilmu Komputer', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'FEB', 'name' => 'Fakultas Ekonomi dan Bisnis', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 2. Programs
        DB::table('programs')->insert([
            ['faculty_id' => 1, 'code' => 'SI', 'name' => 'Sistem Informasi', 'created_at' => $now, 'updated_at' => $now],
            ['faculty_id' => 1, 'code' => 'TI', 'name' => 'Teknik Informatika', 'created_at' => $now, 'updated_at' => $now],
            ['faculty_id' => 2, 'code' => 'MAN', 'name' => 'Manajemen', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 3. Courses
        DB::table('courses')->insert([
            ['program_id' => 1, 'catalog_nbr' => 'SI101', 'course_id' => 'CRSE1001', 'name' => 'Pengantar Sistem Informasi', 'created_at' => $now, 'updated_at' => $now],
            ['program_id' => 1, 'catalog_nbr' => 'SI102', 'course_id' => 'CRSE1002', 'name' => 'Basis Data', 'created_at' => $now, 'updated_at' => $now],
            ['program_id' => 2, 'catalog_nbr' => 'TI201', 'course_id' => 'CRSE2001', 'name' => 'Algoritma & Pemrograman', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 4. Class Sections
        DB::table('class_sections')->insert([
            ['course_id' => 1, 'class_section' => 'A', 'semester' => '2024/2025 Ganjil', 'created_at' => $now, 'updated_at' => $now],
            ['course_id' => 1, 'class_section' => 'B', 'semester' => '2024/2025 Ganjil', 'created_at' => $now, 'updated_at' => $now],
            ['course_id' => 2, 'class_section' => 'A', 'semester' => '2024/2025 Genap', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 5. Users (dosen, ctl, kaprodi, admin)
        DB::table('users')->insert([
            ['emplid' => 'D001', 'name' => 'Budi Santoso', 'email' => 'budi@uph.edu', 'password' => Hash::make('password'), 'role' => 'dosen', 'created_at' => $now, 'updated_at' => $now],
            ['emplid' => 'CTL001', 'name' => 'Siti Rahma', 'email' => 'siti@uph.edu', 'password' => Hash::make('password'), 'role' => 'ctl', 'created_at' => $now, 'updated_at' => $now],
            ['emplid' => 'KAP001', 'name' => 'Andi Wijaya', 'email' => 'andi@uph.edu', 'password' => Hash::make('password'), 'role' => 'kaprodi', 'created_at' => $now, 'updated_at' => $now],
            ['emplid' => 'ADM001', 'name' => 'Admin User', 'email' => 'admin@uph.edu', 'password' => Hash::make('password'), 'role' => 'admin', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 6. Lecturer-ClassSection mapping
        DB::table('lecturer_class_section')->insert([
            ['lecturer_id' => 1, 'class_section_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['lecturer_id' => 1, 'class_section_id' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 7. RPS (dummy data)
        DB::table('rps')->insert([
    'class_section_id' => 1,   // asumsi ada class_section ID 1
    'lecturer_id' => 2,        // asumsi ada user dosen ID 2
    'title' => 'RPS Pengantar SI',
    'description' => 'Draft awal RPS Pengantar SI',
    'file_path' => null,
    'status' => 'submitted',
    'created_at' => now(),
    'updated_at' => now(),
]);


        // 8. Reviews
        DB::table('reviews')->insert([
            ['rps_id' => 1, 'reviewer_id' => 2, 'comments' => 'Mohon tambahkan referensi terbaru.', 'status' => 'reviewed', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 9. Approvals
        DB::table('approvals')->insert([
            ['rps_id' => 1, 'approver_id' => 3, 'status' => 'pending', 'comments' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 10. Activity Logs
        DB::table('activity_logs')->insert([
            ['user_id' => 1, 'action' => 'Submitted RPS Pengantar SI', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 2, 'action' => 'Reviewed RPS Pengantar SI', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
