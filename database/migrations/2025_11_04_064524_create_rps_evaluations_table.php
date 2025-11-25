<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rps_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rps_id')->constrained('rps')->onDelete('cascade');
            $table->foreignId('assessment_category_id')->constrained('rps_assessment_categories')->onDelete('cascade');
            $table->text('method')->nullable();    // bentuk penilaian
            $table->text('criteria')->nullable();  // kriteria/rubrik singkat
            $table->decimal('weight_percent', 5, 2)->default(0); // diset dari Step 3
            $table->unsignedSmallInteger('order_no')->nullable();
            $table->timestamps();

            $table->unique(['rps_id', 'assessment_category_id'], 'rps_eval_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('rps_evaluations');
    }
};
