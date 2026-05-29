<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clearance_analytics', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->index();
            $table->unsignedInteger('total_students')->default(0);
            $table->unsignedInteger('cleared_count')->default(0);
            $table->unsignedInteger('pending_count')->default(0);
            $table->unsignedInteger('partially_cleared_count')->default(0);
            $table->unsignedInteger('disputed_count')->default(0);
            $table->unsignedInteger('validating_count')->default(0);
            $table->decimal('clearance_rate', 5, 2)->default(0.00);
            $table->json('module_breakdown')->nullable(); // per-module cleared counts
            $table->json('grade_breakdown')->nullable();  // per-grade-level counts
            $table->timestamps();

            $table->unique('snapshot_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clearance_analytics');
    }
};
