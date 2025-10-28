<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rps_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rps_id')->constrained('rps')->onDelete('cascade');
            $table->foreignId('sub_clo_id')->constrained('rps_sub_clos')->onDelete('cascade');
            $table->string('type');    // Quiz, Assignment, Exam, Project, etc.
            $table->integer('weight'); // Bobot dalam %
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('rps_assessments');
    }
};
