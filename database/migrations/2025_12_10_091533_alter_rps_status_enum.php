<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `rps`
            MODIFY `status` ENUM(
                'draft',
                'submitted',
                'reviewed',
                'need_revision',
                'revision_submitted',
                'approved',
                'not_approved'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `rps`
            MODIFY `status` ENUM(
                'draft',
                'submitted',
                'reviewed',
                'approved',
                'rejected'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};
