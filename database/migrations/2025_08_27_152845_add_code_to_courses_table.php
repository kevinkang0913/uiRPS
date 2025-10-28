<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Pastikan tabelnya ada
        if (!Schema::hasTable('courses')) {
            return;
        }

        // 1) Tambah kolom code (nullable dulu)
        if (!Schema::hasColumn('courses', 'code')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('code')->nullable()->after('program_id');
            });
        }

        // 2) Backfill aman TANPA mengandalkan 'title'
        //    -> cukup gunakan pola 'CRS{id}'
        $ids = DB::table('courses')->select('id')->get();
        foreach ($ids as $row) {
            DB::table('courses')
                ->where('id', $row->id)
                ->update(['code' => 'CRS'.$row->id]);
        }

        // 3) Jadikan NOT NULL + UNIQUE
        Schema::table('courses', function (Blueprint $table) {
            // pastikan tidak ada null yang tersisa
            $table->string('code')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'code')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            });
        }
    }
};
