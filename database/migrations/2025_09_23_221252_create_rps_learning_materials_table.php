<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rps_learning_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rps_id')->constrained('rps')->onDelete('cascade');
            $table->string('title');     // Judul materi
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->string('year')->nullable();
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('rps_learning_materials');
    }
};
