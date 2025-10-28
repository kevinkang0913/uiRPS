<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rps', function (Blueprint $table) {
            $table->id();

            // relasi dosen yang membuat RPS
            $table->foreignId('lecturer_id')->constrained('users')->onDelete('cascade');

            // relasi kelas (mata kuliah + section)
            $table->foreignId('class_section_id')->constrained('class_sections')->onDelete('cascade');

            $table->string('title');                // judul RPS (biasanya judul course)
            $table->string('description')->nullable(); // deskripsi singkat
            $table->enum('status', [
                'draft',
                'submitted',
                'revisi',
                'forwarded',
                'approved',
                'rejected'
            ])->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('rps');
    }
};
