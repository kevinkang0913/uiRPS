<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lecturer_class_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecturer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_section_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('lecturer_class_section');
    }
};
