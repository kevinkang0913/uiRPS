<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rps_weekly_plan_outcomes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('weekly_plan_id')->constrained('rps_weekly_plans')->onDelete('cascade');
            $t->foreignId('outcome_id')->constrained('rps_outcomes')->onDelete('cascade'); // CPMK
            $t->decimal('percent',5,2)->default(0); // kalau mau bagi persentase CPMK per minggu
            $t->timestamps();
            $t->unique(['weekly_plan_id','outcome_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('rps_weekly_plan_outcomes');
    }
};
