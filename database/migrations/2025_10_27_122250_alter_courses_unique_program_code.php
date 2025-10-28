<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Hapus unique lama pada 'code'
            // Jika nama index beda di server kamu, lihat catatan di bawah.
            $table->dropUnique('courses_code_unique');

            // Tambahkan unique komposit: program_id + code
            $table->unique(['program_id', 'code'], 'courses_program_code_unique');

            // (Opsional, tapi bagus): unique komposit tambahan jika kamu pakai CRSE_ID
            // $table->unique(['program_id', 'course_id'], 'courses_program_courseid_unique');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Kembalikan seperti semula (tidak direkomendasikan, tapi untuk completeness)
            $table->dropUnique('courses_program_code_unique');
            // $table->dropUnique('courses_program_courseid_unique'); // jika sebelumnya diaktifkan

            $table->unique('code', 'courses_code_unique');
        });
    }
};

