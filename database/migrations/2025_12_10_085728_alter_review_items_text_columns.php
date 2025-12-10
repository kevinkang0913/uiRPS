<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_items', function (Blueprint $table) {
            // ubah dari varchar(255) jadi TEXT
            $table->text('criterion_label')->change();
            $table->text('level_label')->change();
        });
    }

    public function down(): void
    {
        Schema::table('review_items', function (Blueprint $table) {
            // rollback ke varchar(255) kalau perlu
            $table->string('criterion_label', 255)->change();
            $table->string('level_label', 255)->change();
        });
    }
};
