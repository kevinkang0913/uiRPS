<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rps_planner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rps_id')->constrained('rps')->onDelete('cascade');
            $table->integer('week'); // Minggu ke-1 s/d 16
            $table->text('topic')->nullable();
            $table->string('method')->nullable();
            $table->string('assessment')->nullable();
            $table->foreignId('learning_material_id')
                  ->nullable()
                  ->constrained('rps_learning_materials')
                  ->onDelete('set null'); // dropdown dari daftar materi
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('rps_planner');
    }
};
