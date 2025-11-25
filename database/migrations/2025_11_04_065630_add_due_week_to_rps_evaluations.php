<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('rps_evaluations', function (Blueprint $table) {
            $table->unsignedTinyInteger('due_week')->nullable()->after('criteria'); // 1..30
        });
    }
    public function down(): void {
        Schema::table('rps_evaluations', function (Blueprint $table) {
            $table->dropColumn('due_week');
        });
    }
};
