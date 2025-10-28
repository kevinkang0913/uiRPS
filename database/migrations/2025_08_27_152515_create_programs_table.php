<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique();   // ACAD_PROG
            $table->string('name');            // Nama program studi
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('programs');
    }
};
