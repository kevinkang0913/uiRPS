<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('rps_plos','order_no')) {
            Schema::table('rps_plos', fn(Blueprint $t) => $t->dropColumn('order_no'));
        }
        if (Schema::hasColumn('rps_outcomes','order_no')) {
            Schema::table('rps_outcomes', fn(Blueprint $t) => $t->dropColumn('order_no'));
        }
        if (Schema::hasColumn('rps_sub_clos','order_no')) {
            Schema::table('rps_sub_clos', fn(Blueprint $t) => $t->dropColumn('order_no'));
        }
    }

    public function down(): void
    {
        Schema::table('rps_plos', fn(Blueprint $t) => $t->unsignedInteger('order_no')->nullable()->after('description'));
        Schema::table('rps_outcomes', fn(Blueprint $t) => $t->unsignedInteger('order_no')->nullable()->after('description'));
        Schema::table('rps_sub_clos', fn(Blueprint $t) => $t->unsignedInteger('order_no')->nullable()->after('description'));
    }
};
