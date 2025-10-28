<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rps_id')->constrained('rps')->onDelete('cascade');
    $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade'); // CTL
    $table->text('comments')->nullable();
    $table->enum('status', ['revisi', 'forwarded'])->default('revisi'); 
    $table->timestamps();
});

    }

    public function down(): void {
        Schema::dropIfExists('reviews');
    }
};
