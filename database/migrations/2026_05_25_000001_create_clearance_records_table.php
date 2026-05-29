<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clearance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('status', [
                'pending',
                'validating',
                'partially_cleared',
                'cleared',
                'disputed',
                'expired',
            ])->default('pending')->index();
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->unsignedTinyInteger('modules_cleared')->default(0);
            $table->unsignedTinyInteger('modules_total')->default(4);
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamp('cleared_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('correlation_id', 64)->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clearance_records');
    }
};
