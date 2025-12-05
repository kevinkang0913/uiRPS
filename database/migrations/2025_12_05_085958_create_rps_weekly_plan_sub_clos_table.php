<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rps_weekly_plan_sub_clos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('weekly_plan_id')
                ->constrained('rps_weekly_plans')
                ->cascadeOnDelete();

            $table->foreignId('sub_clo_id')
                ->constrained('rps_sub_clos')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['weekly_plan_id', 'sub_clo_id'], 'wp_subclo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rps_weekly_plan_sub_clos');
    }
};
