<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rps_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rps_id');

            $table->longText('class_policy')->nullable();
            $table->longText('contract_text')->nullable();

            $table->timestamps();

            $table->foreign('rps_id')
                  ->references('id')->on('rps')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rps_contracts');
    }
};
