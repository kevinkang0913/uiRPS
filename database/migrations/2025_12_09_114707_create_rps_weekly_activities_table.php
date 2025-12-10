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
        Schema::create('rps_weekly_activities', function (Blueprint $table) {
            $table->id();

            // relasi ke rps_weekly_plans
            $table->unsignedBigInteger('weekly_plan_id');

            // mode aktivitas: luring / daring
            $table->enum('mode', ['luring', 'daring']);

            // tipe aktivitas: KM / PB / PT (boleh null kalau kamu mau isi deskripsi dulu)
            $table->enum('activity_type', ['KM', 'PB', 'PT'])->nullable();

            // durasi bebas (text), contoh: "2 x 50'", "30 menit"
            $table->string('duration')->nullable();

            // deskripsi aktivitas
            $table->text('description')->nullable();

            // urutan aktivitas dalam 1 minggu
            $table->unsignedTinyInteger('order_no')->default(1);

            $table->timestamps();

            $table->foreign('weekly_plan_id')
                ->references('id')
                ->on('rps_weekly_plans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rps_weekly_activities');
    }
};
