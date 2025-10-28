<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Program;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $program = Program::first();
        if (!$program) {
            $this->command->warn('⚠️ Program belum ada. Jalankan ProgramSeeder dulu.');
            return;
        }

        $courses = [
            // code, name menyesuaikan kolom yang ada
            ['code' => 'SI101', 'name' => 'Pengantar Sistem Informasi', 'catalog_nbr' => '101', 'course_id' => 'SI-101'],
            ['code' => 'SI202', 'name' => 'Analisis & Perancangan Sistem', 'catalog_nbr' => '202', 'course_id' => 'SI-202'],
            ['code' => 'SI303', 'name' => 'Manajemen Basis Data',       'catalog_nbr' => '303', 'course_id' => 'SI-303'],
        ];

        foreach ($courses as $c) {
            Course::updateOrCreate(
                ['code' => $c['code']], // unique key
                [
                    'program_id'  => $program->id,
                    'name'        => $c['name'],
                    'catalog_nbr' => $c['catalog_nbr'] ?? null,
                    'course_id'   => $c['course_id']   ?? null,
                ]
            );
        }

        $this->command->info('✅ CourseSeeder selesai dengan skema: code, name, catalog_nbr, course_id.');
    }
}
