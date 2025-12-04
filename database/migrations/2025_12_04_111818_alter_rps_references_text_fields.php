<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rps_references', function (Blueprint $table) {
            // referensi bisa panjang → TEXT
            $table->text('title')->change();

            // url bisa panjang → TEXT atau varchar(500)
            $table->text('url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rps_references', function (Blueprint $table) {
            // rollback ke ukuran lama
            $table->string('title', 500)->change();
            $table->string('url', 500)->nullable()->change();
        });
    }
};
