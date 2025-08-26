<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('rps', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
        $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
        $table->string('title');
        $table->text('description');
        $table->string('file_path')->nullable(); // path ke file RPS (PDF/DOCX)
        $table->enum('status', ['submitted','reviewed','approved','rejected'])->default('submitted');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rps');
    }
};
