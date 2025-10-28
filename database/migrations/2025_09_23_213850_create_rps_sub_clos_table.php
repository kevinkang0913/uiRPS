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
        Schema::create('rps_sub_clos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('outcome_id')->constrained('rps_outcomes')->onDelete('cascade');
    $table->text('description'); // isi Sub-CLO
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rps_sub_clos');
    }
};
