<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rps_outcomes', function (Blueprint $table) {
            // sesuaikan posisi AFTER kalau mau
            $table->decimal('weight_percent', 5, 2)
                  ->nullable()
                  ->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('rps_outcomes', function (Blueprint $table) {
            $table->dropColumn('weight_percent');
        });
    }
};
