<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rps_assessments', function (Blueprint $t) {
            if (Schema::hasColumn('rps_assessments', 'sub_clo_id')) {
                $t->unsignedBigInteger('sub_clo_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rps_assessments', function (Blueprint $t) {
            if (Schema::hasColumn('rps_assessments', 'sub_clo_id')) {
                $t->unsignedBigInteger('sub_clo_id')->nullable(false)->change();
            }
        });
    }
};
