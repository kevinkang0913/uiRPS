<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tambahkan kolom course_id kalau belum ada
        if (!Schema::hasColumn('courses', 'course_id')) {
            Schema::table('courses', function (Blueprint $table) {
                // nullable dulu supaya aman terhadap data existing
                $table->string('course_id')->nullable()->after('catalog_nbr');
            });
        }

        // (Opsional) Index biasa untuk bantu query, nanti tetap bisa coexist dengan unique
        // Hapus bagian ini jika tidak pakai doctrine/dbal untuk cek index.
        /*
        if (! $this->hasIndex('courses', 'courses_course_id_idx')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->index('course_id', 'courses_course_id_idx');
            });
        }
        */
    }

    public function down(): void
    {
        // Biarkan kolom tetap ada demi kompatibilitas (down dibiarkan kosong atau hanya drop index opsional)
        /*
        if ($this->hasIndex('courses', 'courses_course_id_idx')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropIndex('courses_course_id_idx');
            });
        }
        */
    }

    // Uncomment kalau kamu ingin cek index via doctrine/dbal
    /*
    private function hasIndex(string $table, string $index): bool {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $doctrineTable = $sm->introspectTable($table);
        foreach ($doctrineTable->getIndexes() as $idx) {
            if ($idx->getName() === $index) return true;
        }
        return false;
    }
    */
};
