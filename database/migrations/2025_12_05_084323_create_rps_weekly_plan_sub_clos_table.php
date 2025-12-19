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
            $table->unsignedBigInteger('weekly_plan_id');
            $table->unsignedBigInteger('sub_clo_id');
            $table->timestamps();

            $table->foreign('weekly_plan_id')
                ->references('id')->on('rps_weekly_plans')
                ->onDelete('cascade');

            $table->foreign('sub_clo_id')
                ->references('id')->on('rps_sub_clos')
                ->onDelete('cascade');

            $table->unique(['weekly_plan_id','sub_clo_id'], 'wk_subclo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rps_weekly_plan_sub_clos');
    }
};
