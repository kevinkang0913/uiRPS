<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Lepas FOREIGN KEY dulu kalau ada
        //    (nama default Laravel: rps_<column>_foreign)
        if (Schema::hasColumn('rps', 'lecturer_id')) {
            try { DB::statement('ALTER TABLE `rps` DROP FOREIGN KEY `rps_lecturer_id_foreign`'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE `rps` DROP INDEX `rps_lecturer_id_foreign`'); } catch (\Throwable $e) {}
        }
        if (Schema::hasColumn('rps', 'class_section_id')) {
            try { DB::statement('ALTER TABLE `rps` DROP FOREIGN KEY `rps_class_section_id_foreign`'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE `rps` DROP INDEX `rps_class_section_id_foreign`'); } catch (\Throwable $e) {}
        }

        // 2) Drop kolom lama yang tidak dipakai
        Schema::table('rps', function (Blueprint $t) {
            if (Schema::hasColumn('rps', 'lecturer_id')) {
                $t->dropColumn('lecturer_id');
            }
            if (Schema::hasColumn('rps', 'class_section_id')) {
                $t->dropColumn('class_section_id');
            }
            if (Schema::hasColumn('rps', 'title')) {
                $t->dropColumn('title');
            }
            if (Schema::hasColumn('rps', 'description')) {
                $t->dropColumn('description');
            }
        });

        // 3) Tambah kolom baru (versi full fields)
        Schema::table('rps', function (Blueprint $t) {
            if (!Schema::hasColumn('rps', 'course_id')) {
                $t->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('rps', 'program_id')) {
                $t->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            }

            if (!Schema::hasColumn('rps', 'academic_year')) {
                $t->string('academic_year', 20)->nullable();
            }
            if (!Schema::hasColumn('rps', 'semester')) {
                $t->unsignedTinyInteger('semester')->nullable();
            }
            if (!Schema::hasColumn('rps', 'sks')) {
                $t->unsignedTinyInteger('sks')->nullable();
            }
            if (!Schema::hasColumn('rps', 'delivery_mode')) {
                $t->string('delivery_mode', 20)->nullable();
            }
            if (!Schema::hasColumn('rps', 'language')) {
                $t->string('language', 50)->nullable();
            }
            if (!Schema::hasColumn('rps', 'lecturers')) {
                $t->json('lecturers')->nullable();
            }
            if (!Schema::hasColumn('rps', 'prerequisites')) {
                $t->json('prerequisites')->nullable();
            }
            if (!Schema::hasColumn('rps', 'course_policies')) {
                $t->json('course_policies')->nullable();
            }
            if (!Schema::hasColumn('rps', 'submitted_by')) {
                $t->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('rps', 'submitted_at')) {
                $t->timestamp('submitted_at')->nullable();
            }
        });

        // 4) Ubah ENUM status menjadi: draft/submitted/reviewed/approved/rejected
        //    Pakai statement supaya tidak tergantung doctrine/dbal
        try {
            DB::statement("ALTER TABLE `rps` MODIFY `status`
                ENUM('draft','submitted','reviewed','approved','rejected')
                NOT NULL DEFAULT 'draft'");
        } catch (\Throwable $e) {
            // Jika gagal karena bukan ENUM (mis: varchar), abaikan
        }
    }

    public function down(): void
    {
        // Balikkan perubahan (opsional, minimal saja)
        Schema::table('rps', function (Blueprint $t) {
            // drop kolom baru
            if (Schema::hasColumn('rps', 'submitted_at'))   $t->dropColumn('submitted_at');
            if (Schema::hasColumn('rps', 'submitted_by'))   $t->dropConstrainedForeignId('submitted_by');
            if (Schema::hasColumn('rps', 'course_policies'))$t->dropColumn('course_policies');
            if (Schema::hasColumn('rps', 'prerequisites'))  $t->dropColumn('prerequisites');
            if (Schema::hasColumn('rps', 'lecturers'))      $t->dropColumn('lecturers');
            if (Schema::hasColumn('rps', 'language'))       $t->dropColumn('language');
            if (Schema::hasColumn('rps', 'delivery_mode'))  $t->dropColumn('delivery_mode');
            if (Schema::hasColumn('rps', 'sks'))            $t->dropColumn('sks');
            if (Schema::hasColumn('rps', 'semester'))       $t->dropColumn('semester');
            if (Schema::hasColumn('rps', 'academic_year'))  $t->dropColumn('academic_year');

            if (Schema::hasColumn('rps', 'program_id'))     $t->dropConstrainedForeignId('program_id');
            if (Schema::hasColumn('rps', 'course_id'))      $t->dropConstrainedForeignId('course_id');
        });

        // Kembalikan ENUM lama (jika perlu)
        try {
            DB::statement("ALTER TABLE `rps` MODIFY `status`
                ENUM('draft','submitted','revisi','forwarded','approved','rejected')
                NOT NULL DEFAULT 'draft'");
        } catch (\Throwable $e) {}

        // (Opsional) tambahkan kembali kolom lama jika ingin benar-benar rollback
        Schema::table('rps', function (Blueprint $t) {
            if (!Schema::hasColumn('rps', 'lecturer_id')) {
                $t->unsignedBigInteger('lecturer_id')->nullable();
            }
            if (!Schema::hasColumn('rps', 'class_section_id')) {
                $t->unsignedBigInteger('class_section_id')->nullable();
            }
            if (!Schema::hasColumn('rps', 'title')) {
                $t->string('title')->nullable();
            }
            if (!Schema::hasColumn('rps', 'description')) {
                $t->string('description')->nullable();
            }
        });
    }
};
