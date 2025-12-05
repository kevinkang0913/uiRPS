<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rps_weekly_plans', function (Blueprint $table) {
            // 1) Relasi ke Sub-CPMK
            $table->unsignedBigInteger('sub_clo_id')
                ->nullable()
                ->after('rps_id');

            // 2) Kalau mau bedakan sesi dalam 1 minggu (opsional)
            $table->unsignedTinyInteger('session_no')
                ->nullable()
                ->after('week_no');

            // 3) Bidang indikator & penilaian
            $table->text('indicator')
                ->nullable()
                ->after('topic');

            $table->string('assessment_technique', 100)
                ->nullable()
                ->after('indicator');

            $table->text('assessment_criteria')
                ->nullable()
                ->after('assessment_technique');

            // 4) Aktivitas luring & daring
            $table->text('learning_in')      // aktivitas tatap muka / luring
                ->nullable()
                ->after('student_activity');

            $table->text('learning_online')  // aktivitas daring
                ->nullable()
                ->after('learning_in');

            // 5) Relasi ke referensi (dropdown dari Step 4)
            $table->unsignedBigInteger('reference_id')
                ->nullable()
                ->after('references');

            // Foreign key
            $table->foreign('sub_clo_id')
                ->references('id')
                ->on('rps_sub_clos')
                ->onDelete('set null');

            $table->foreign('reference_id')
                ->references('id')
                ->on('rps_references')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('rps_weekly_plans', function (Blueprint $table) {
            // drop FK dulu
            if (Schema::hasColumn('rps_weekly_plans', 'sub_clo_id')) {
                $table->dropForeign(['sub_clo_id']);
            }
            if (Schema::hasColumn('rps_weekly_plans', 'reference_id')) {
                $table->dropForeign(['reference_id']);
            }

            // lalu drop kolom
            $table->dropColumn([
                'sub_clo_id',
                'session_no',
                'indicator',
                'assessment_technique',
                'assessment_criteria',
                'learning_in',
                'learning_online',
                'reference_id',
            ]);
        });
    }
};
