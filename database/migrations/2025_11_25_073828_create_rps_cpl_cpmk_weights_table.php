<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rps_cpl_cpmk_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rps_id')->constrained('rps')->onDelete('cascade');
            $table->foreignId('plo_id')->constrained('rps_plos')->onDelete('cascade');
            $table->foreignId('outcome_id')->constrained('rps_outcomes')->onDelete('cascade');
            $table->decimal('percent', 5, 2)->default(0); // 0–100
            $table->timestamps();

            $table->unique(['rps_id', 'plo_id', 'outcome_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rps_cpl_cpmk_weights');
    }
};
