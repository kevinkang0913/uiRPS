<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rps_plos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rps_id')->constrained('rps')->onDelete('cascade');
    $table->text('description'); // isi PLO
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rps_plos');
    }
};
