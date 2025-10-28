<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->string('catalog_nbr');    // CATALOG_NBR
            $table->string('course_id');      // CRSE_ID
            $table->string('name');           // DESCR
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('courses');
    }
};
