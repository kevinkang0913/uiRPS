<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('rps_sub_clos', function (Blueprint $table) {
        $table->decimal('weight_percent', 8, 4)->nullable()->after('description');
    });
}

public function down(): void
{
    Schema::table('rps_sub_clos', function (Blueprint $table) {
        $table->dropColumn('weight_percent');
    });
}

};
