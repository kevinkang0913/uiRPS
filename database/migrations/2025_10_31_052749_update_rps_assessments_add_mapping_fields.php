<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rps_assessments', function (Blueprint $t) {
            // tambahkan kolom jika belum ada
            if (!Schema::hasColumn('rps_assessments', 'assessment_category_id')) {
                $t->foreignId('assessment_category_id')
                  ->after('rps_id')
                  ->constrained('rps_assessment_categories')
                  ->cascadeOnDelete();
            }
            if (!Schema::hasColumn('rps_assessments', 'desc')) {
                $t->string('desc')->nullable()->after('assessment_category_id');
            }
            if (!Schema::hasColumn('rps_assessments', 'weight_percent')) {
                $t->decimal('weight_percent', 5, 2)->default(0)->after('desc');
            }
            if (!Schema::hasColumn('rps_assessments', 'due_week')) {
                $t->unsignedSmallInteger('due_week')->nullable()->after('weight_percent');
            }
            if (!Schema::hasColumn('rps_assessments', 'order_no')) {
                $t->unsignedInteger('order_no')->default(1)->after('due_week');
            }

            // unique kombinasi rps + kategori (hindari duplikat baris)
            $t->unique(['rps_id','assessment_category_id'], 'rps_assessments_unique');
        });
    }

    public function down(): void
    {
        Schema::table('rps_assessments', function (Blueprint $t) {
            // drop index jika ada
            try { $t->dropUnique('rps_assessments_unique'); } catch (\Throwable $e) {}

            // drop kolom satu per satu bila ada
            foreach (['order_no','due_week','weight_percent','desc','assessment_category_id'] as $col) {
                if (Schema::hasColumn('rps_assessments', $col)) {
                    if ($col === 'assessment_category_id') {
                        $t->dropConstrainedForeignId('assessment_category_id');
                    } else {
                        $t->dropColumn($col);
                    }
                }
            }
        });
    }
};
