<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasColumn('courses', 'course_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('course_id');
            });
        }
    }
    public function down(): void {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('course_id')->nullable()->after('catalog_nbr');
        });
    }
};
