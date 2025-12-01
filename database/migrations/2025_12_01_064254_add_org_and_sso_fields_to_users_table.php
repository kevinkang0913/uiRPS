<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Organisasi: fakultas & prodi
            $table->foreignId('faculty_id')
                ->nullable()
                ->after('role')
                ->constrained('faculties')
                ->nullOnDelete();

            $table->foreignId('program_id')
                ->nullable()
                ->after('faculty_id')
                ->constrained('programs')
                ->nullOnDelete();

            // SSO (opsional, tapi kepake nanti)
            $table->string('sso_provider', 50)
                ->nullable()
                ->after('emplid');

            $table->string('sso_subject')
                ->nullable()
                ->unique()
                ->after('sso_provider');

            // Status aktif / tidak aktif
            $table->boolean('is_active')
                ->default(true)
                ->after('sso_subject');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // hapus FK dulu baru kolomnya
            $table->dropForeign(['faculty_id']);
            $table->dropForeign(['program_id']);

            $table->dropColumn([
                'faculty_id',
                'program_id',
                'sso_provider',
                'sso_subject',
                'is_active',
            ]);
        });
    }
};
