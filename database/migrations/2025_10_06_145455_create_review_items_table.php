<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('review_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->onDelete('cascade');
            $table->string('criterion_key');         // c1, c2, ...
            $table->string('criterion_label');       // cache label
            $table->unsignedInteger('weight');       // cache weight
            $table->unsignedInteger('level_index');  // index levels (0..n-1)
            $table->string('level_label');           // cache label level
            $table->unsignedInteger('level_score');  // 0..100
            $table->unsignedInteger('weighted_score'); // level_score * weight / 100
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // tambahkan kolom ringkasan ke reviews (jika belum ada)
        if (!Schema::hasColumn('reviews','total_score')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->unsignedInteger('total_score')->nullable()->after('status');
                $table->string('rubric_version')->nullable()->after('total_score');
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('review_items');
        // (opsional) drop kolom tambahan di reviews kalau mau bersih total
    }
};
