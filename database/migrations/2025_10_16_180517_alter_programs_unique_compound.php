<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('programs', function (Blueprint $table) {
            // hapus unique lama bila ada
            try { $table->dropUnique('programs_code_unique'); } catch (\Throwable $e) {}
            // tambah unique gabungan
            $table->unique(['faculty_id','code'], 'programs_faculty_code_unique');
        });
    }

    public function down(): void {
        Schema::table('programs', function (Blueprint $table) {
            try { $table->dropUnique('programs_faculty_code_unique'); } catch (\Throwable $e) {}
            $table->unique('code'); // fallback
        });
    }
};
