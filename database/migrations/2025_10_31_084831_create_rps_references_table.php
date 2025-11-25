<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rps_references', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rps_id')->constrained('rps')->cascadeOnDelete();
            $t->enum('type', ['utama','pendukung'])->default('utama'); // sesuai template UPH
            $t->string('author', 255)->nullable();
            $t->string('year', 10)->nullable();
            $t->string('title', 500)->nullable();
            $t->string('publisher', 255)->nullable();
            $t->string('city', 255)->nullable();
            $t->string('url', 500)->nullable();
            $t->string('isbn_issn', 50)->nullable();
            $t->unsignedInteger('order_no')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rps_references');
    }
};
