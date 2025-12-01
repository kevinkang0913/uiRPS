<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rps', function (Blueprint $table) {
            $table->string('class_number')->nullable();
            $table->string('learning_activity_type')->nullable();
            $table->string('course_category')->nullable();
            $table->text('short_description')->nullable();
            $table->string('prerequisite_courses')->nullable();
            $table->string('prerequisite_for_courses')->nullable();
            $table->text('study_materials')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rps', function (Blueprint $table) {
            $table->dropColumn([
                'class_number',
                'learning_activity_type',
                'course_category',
                'short_description',
                'prerequisite_courses',
                'prerequisite_for_courses',
                'study_materials',
            ]);
        });
    }
};
