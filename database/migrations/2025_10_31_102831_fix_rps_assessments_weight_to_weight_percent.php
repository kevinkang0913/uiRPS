<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rps_assessments', function (Blueprint $t) {
            if (!Schema::hasColumn('rps_assessments', 'weight_percent')) {
                $t->decimal('weight_percent', 5, 2)->default(0)->after('assessment_category_id');
            }
        });

        // Copy nilai lama jika kolom `weight` ada
        if (Schema::hasColumn('rps_assessments', 'weight')) {
            DB::statement('UPDATE rps_assessments SET weight_percent = weight');
            // Hapus kolom weight biar tidak bentrok lagi
            Schema::table('rps_assessments', function (Blueprint $t) {
                $t->dropColumn('weight');
            });
        }
    }

    public function down(): void
    {
        // (opsional) balikin lagi
        if (!Schema::hasColumn('rps_assessments', 'weight')) {
            Schema::table('rps_assessments', function (Blueprint $t) {
                $t->decimal('weight', 5, 2)->default(0);
            });
            DB::statement('UPDATE rps_assessments SET weight = weight_percent');
        }

        Schema::table('rps_assessments', function (Blueprint $t) {
            if (Schema::hasColumn('rps_assessments', 'weight_percent')) {
                $t->dropColumn('weight_percent');
            }
        });
    }
};
