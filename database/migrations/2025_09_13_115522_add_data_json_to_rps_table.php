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
    Schema::table('rps', function (Blueprint $table) {
        // Hapus after('description') biar aman
        $table->longText('data_json')->nullable();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rps', function (Blueprint $table) {
            $table->dropColumn('data_json');
        });
    }
};
