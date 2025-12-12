<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_lecturers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Editing vs non-editing (per course)
            $table->boolean('can_edit')->default(false);

            // Dosen penanggung jawab (PIC) per course
            $table->boolean('is_responsible')->default(false);

            $table->timestamps();

            // 1 dosen tidak boleh dobel di course yang sama
            $table->unique(['course_id', 'user_id']);

            // untuk query cepat: course by dosen / dosen editing
            $table->index(['user_id', 'can_edit']);
            $table->index(['course_id', 'is_responsible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_lecturers');
    }
};
