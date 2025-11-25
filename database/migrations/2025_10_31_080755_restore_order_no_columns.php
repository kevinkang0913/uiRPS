<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1️⃣ RPS_PLOS
        if (Schema::hasTable('rps_plos') && !Schema::hasColumn('rps_plos', 'order_no')) {
            Schema::table('rps_plos', function (Blueprint $t) {
                $t->unsignedInteger('order_no')->nullable()->after('description');
            });
        }

        // 2️⃣ RPS_OUTCOMES
        if (Schema::hasTable('rps_outcomes') && !Schema::hasColumn('rps_outcomes', 'order_no')) {
            Schema::table('rps_outcomes', function (Blueprint $t) {
                $t->unsignedInteger('order_no')->nullable()->after('description');
            });
        }

        // 3️⃣ RPS_SUB_CLOS
        if (Schema::hasTable('rps_sub_clos') && !Schema::hasColumn('rps_sub_clos', 'order_no')) {
            Schema::table('rps_sub_clos', function (Blueprint $t) {
                $t->unsignedInteger('order_no')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        // rollback = drop lagi
        Schema::table('rps_plos', function (Blueprint $t) {
            if (Schema::hasColumn('rps_plos', 'order_no')) {
                $t->dropColumn('order_no');
            }
        });
        Schema::table('rps_outcomes', function (Blueprint $t) {
            if (Schema::hasColumn('rps_outcomes', 'order_no')) {
                $t->dropColumn('order_no');
            }
        });
        Schema::table('rps_sub_clos', function (Blueprint $t) {
            if (Schema::hasColumn('rps_sub_clos', 'order_no')) {
                $t->dropColumn('order_no');
            }
        });
    }
};
