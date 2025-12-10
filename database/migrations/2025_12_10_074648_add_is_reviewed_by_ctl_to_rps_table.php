<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('rps', function (Blueprint $table) {
        $table->boolean('is_reviewed_by_ctl')
              ->default(false)
              ->after('status');
    });
}

public function down()
{
    Schema::table('rps', function (Blueprint $table) {
        $table->dropColumn('is_reviewed_by_ctl');
    });
}

};
