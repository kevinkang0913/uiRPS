<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /**
         * === 1️⃣ RPS_PLOS === (Program Learning Outcomes / CPL)
         * pastikan ada kolom code, description, order_no, rps_id
         */
        Schema::table('rps_plos', function (Blueprint $t) {
            if (!Schema::hasColumn('rps_plos', 'rps_id')) {
                $t->foreignId('rps_id')->nullable()->constrained('rps')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('rps_plos', 'code')) {
                $t->string('code', 50)->nullable();
            }
            if (!Schema::hasColumn('rps_plos', 'description')) {
                $t->text('description')->nullable();
            }
            if (!Schema::hasColumn('rps_plos', 'order_no')) {
                $t->unsignedSmallInteger('order_no')->default(1);
            }
        });

        /**
         * === 2️⃣ RPS_OUTCOMES === (Course Learning Outcomes / CPMK)
         * Tambahkan kolom foreign key ke PLO dan field yang kurang
         */
        Schema::table('rps_outcomes', function (Blueprint $t) {
            if (!Schema::hasColumn('rps_outcomes', 'rps_id')) {
                $t->foreignId('rps_id')->nullable()->constrained('rps')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('rps_outcomes', 'plo_id')) {
                $t->foreignId('plo_id')->nullable()->constrained('rps_plos')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('rps_outcomes', 'no')) {
                $t->unsignedSmallInteger('no')->default(1);
            }
            if (!Schema::hasColumn('rps_outcomes', 'description')) {
                $t->text('description')->nullable();
            }
            if (!Schema::hasColumn('rps_outcomes', 'order_no')) {
                $t->unsignedSmallInteger('order_no')->default(1);
            }
        });

        /**
         * === 3️⃣ RPS_SUB_CLOS === (Sub-Course Learning Outcomes / sub-CPMK)
         * Tambahkan kolom relasi ke outcomes & field yang kurang
         */
        Schema::table('rps_sub_clos', function (Blueprint $t) {
            if (!Schema::hasColumn('rps_sub_clos', 'rps_id')) {
                $t->foreignId('rps_id')->nullable()->constrained('rps')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('rps_sub_clos', 'outcome_id')) {
                $t->foreignId('outcome_id')->nullable()->constrained('rps_outcomes')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('rps_sub_clos', 'no')) {
                $t->unsignedSmallInteger('no')->default(1);
            }
            if (!Schema::hasColumn('rps_sub_clos', 'description')) {
                $t->text('description')->nullable();
            }
            if (!Schema::hasColumn('rps_sub_clos', 'order_no')) {
                $t->unsignedSmallInteger('order_no')->default(1);
            }
        });
    }

    public function down(): void
    {
        // rollback hanya drop kolom baru jika ingin
        Schema::table('rps_plos', function (Blueprint $t) {
            foreach (['rps_id','code','description','order_no'] as $col)
                if (Schema::hasColumn('rps_plos',$col)) $t->dropColumn($col);
        });
        Schema::table('rps_outcomes', function (Blueprint $t) {
            foreach (['rps_id','plo_id','no','description','order_no'] as $col)
                if (Schema::hasColumn('rps_outcomes',$col)) $t->dropColumn($col);
        });
        Schema::table('rps_sub_clos', function (Blueprint $t) {
            foreach (['rps_id','outcome_id','no','description','order_no'] as $col)
                if (Schema::hasColumn('rps_sub_clos',$col)) $t->dropColumn($col);
        });
    }
};
