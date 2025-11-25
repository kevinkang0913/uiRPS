<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rps_weekly_plans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rps_id')->constrained('rps')->onDelete('cascade');
            $t->unsignedInteger('week_no');                  // Minggu ke-
            $t->string('topic');                             // Topik/Materi
            $t->text('sub_topics')->nullable();              // Subtopik/Rincian
            $t->string('learning_method')->nullable();       // Ceramah, diskusi, PBL, dll.
            $t->text('student_activity')->nullable();        // Aktivitas mahasiswa
            $t->string('media_tools')->nullable();           // Media/alat
            $t->decimal('weight_percent',5,2)->default(0);   // Bobot minggu (%), opsional
            $t->text('references')->nullable();              // Referensi (free text)
            $t->unsignedInteger('order_no')->default(1);
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('rps_weekly_plans');
    }
};
