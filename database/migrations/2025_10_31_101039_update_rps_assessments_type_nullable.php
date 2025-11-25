<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rps_assessments', function (Blueprint $t) {
            if (Schema::hasColumn('rps_assessments', 'type')) {
                $t->string('type', 50)->nullable()->default(null)->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rps_assessments', function (Blueprint $t) {
            if (Schema::hasColumn('rps_assessments', 'type')) {
                $t->string('type', 50)->nullable(false)->change();
            }
        });
    }
};
