<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rps_assessment_categories', function (Blueprint $t) {
            $t->id();
            $t->string('code', 10)->unique();      // PAR, PRO, TG, QZ, UTS, UAS, dll
            $t->string('name', 100);
            $t->string('default_desc')->nullable();
            $t->unsignedInteger('order_no')->default(1);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rps_assessment_categories');
    }
};
