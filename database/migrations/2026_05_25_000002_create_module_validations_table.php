<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clearance_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('module_key', 50)->index();   // enrollease | assesspay | librarysys | gradetrack
            $table->string('module_name', 100);
            $table->enum('status', ['pending', 'cleared', 'failed', 'error', 'timeout'])->default('pending')->index();
            $table->json('response_payload')->nullable();
            $table->text('unresolved_issues')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->unsignedSmallInteger('response_time_ms')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['student_id', 'module_key']);
            $table->index(['clearance_record_id', 'module_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_validations');
    }
};
