<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rps_id')->constrained('rps')->onDelete('cascade');
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->string('action'); // submit, review, forward, approve, reject
    $table->text('notes')->nullable();
    $table->timestamps();
});

    }

    public function down(): void {
        Schema::dropIfExists('activity_logs');
    }
};
