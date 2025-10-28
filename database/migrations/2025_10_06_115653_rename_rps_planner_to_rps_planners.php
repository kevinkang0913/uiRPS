<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('rps_planner') && !Schema::hasTable('rps_planners')) {
            Schema::rename('rps_planner', 'rps_planners');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rps_planners') && !Schema::hasTable('rps_planner')) {
            Schema::rename('rps_planners', 'rps_planner');
        }
    }
};
