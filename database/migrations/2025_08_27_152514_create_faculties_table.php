<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();   // FACULTY
            $table->string('name');            // Nama fakultas
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('faculties');
    }
};
