<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rps', function (Blueprint $table) {
            $table->unsignedBigInteger('cloned_from_id')->nullable()->after('id');
            $table->unsignedInteger('version_no')->default(1)->after('cloned_from_id');
            $table->string('version_group', 64)->nullable()->after('version_no'); // grouping versi
            $table->boolean('is_current')->default(true)->after('version_group');

            $table->index(['version_group', 'version_no']);
            $table->foreign('cloned_from_id')->references('id')->on('rps')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rps', function (Blueprint $table) {
            $table->dropForeign(['cloned_from_id']);
            $table->dropIndex(['version_group', 'version_no']);

            $table->dropColumn(['cloned_from_id','version_no','version_group','is_current']);
        });
    }
};
