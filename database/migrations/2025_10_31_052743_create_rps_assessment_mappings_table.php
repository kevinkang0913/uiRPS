<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rps_assessment_mappings', function (Blueprint $t) {
            $t->id();

            $t->foreignId('rps_id')
              ->constrained('rps')
              ->cascadeOnDelete();

            $t->foreignId('assessment_category_id')
              ->constrained('rps_assessment_categories')
              ->cascadeOnDelete();

            // outcome_id = CPMK (table: rps_outcomes)
            $t->foreignId('outcome_id')
              ->constrained('rps_outcomes')
              ->cascadeOnDelete();

            $t->decimal('percent', 5, 2)->default(0); // 0–100
            $t->timestamps();

            $t->unique(['rps_id','assessment_category_id','outcome_id'], 'rps_mapping_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rps_assessment_mappings');
    }
};
