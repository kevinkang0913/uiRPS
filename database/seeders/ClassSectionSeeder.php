<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassSection;
use App\Models\Course;

class ClassSectionSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->warn('⚠️ Tidak ada course ditemukan. Jalankan CourseSeeder dulu.');
            return;
        }

        foreach ($courses as $course) {
            foreach (['Ganjil 2024', 'Genap 2024'] as $idx => $semester) {
                ClassSection::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'class_section' => $course->code . '-A' . ($idx+1),
                    ],
                    [
                        'semester' => $semester,
                    ]
                );
            }
        }

        $this->command->info('✅ ClassSectionSeeder selesai: tiap course dapat 2 class_section.');
    }
}
