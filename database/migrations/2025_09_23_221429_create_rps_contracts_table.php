<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rps_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rps_id')->constrained('rps')->onDelete('cascade');
            
            $table->text('attendance_policy')->nullable();     // aturan kehadiran
            $table->text('participation_policy')->nullable();  // aturan partisipasi
            $table->text('late_policy')->nullable();           // aturan keterlambatan
            $table->text('grading_policy')->nullable();        // aturan penilaian
            $table->text('extra_rules')->nullable();           // aturan tambahan

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('rps_contracts');
    }
};
