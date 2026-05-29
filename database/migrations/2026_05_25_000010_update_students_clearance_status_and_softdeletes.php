<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Widen clearance_status to include all spec-required statuses
            $table->enum('clearance_status', [
                'pending',
                'validating',
                'partially_cleared',
                'cleared',
                'disputed',
                'expired',
                'in_progress',   // kept for backward compat
                'completed',     // kept for backward compat
                'rejected',      // kept for backward compat
            ])->default('pending')->change();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->enum('clearance_status', ['pending', 'in_progress', 'completed', 'rejected'])
                  ->default('pending')->change();
        });
    }
};
