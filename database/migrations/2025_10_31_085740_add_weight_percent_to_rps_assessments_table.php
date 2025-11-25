<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('rps_assessments') && !Schema::hasColumn('rps_assessments', 'weight_percent')) {
            Schema::table('rps_assessments', function (Blueprint $t) {
                $t->decimal('weight_percent', 5, 2)->default(0)->after('assessment_category_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('rps_assessments', function (Blueprint $t) {
            if (Schema::hasColumn('rps_assessments', 'weight_percent')) {
                $t->dropColumn('weight_percent');
            }
        });
    }
};
