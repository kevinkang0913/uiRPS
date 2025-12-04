<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rps_assessments', function (Blueprint $table) {
            // ubah dari integer jadi string (varchar)
            $table->string('due_week', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rps_assessments', function (Blueprint $table) {
            // balik lagi ke tipe sebelumnya (misal tinyInteger / integer)
            $table->unsignedTinyInteger('due_week')->nullable()->change();
            // kalau sebelumnya bukan unsignedTinyInteger, sesuaikan
        });
    }
};
