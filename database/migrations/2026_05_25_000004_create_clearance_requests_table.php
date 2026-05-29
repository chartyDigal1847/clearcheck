<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clearance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('clearance_record_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['initial', 'refresh', 'dispute'])->default('initial');
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued')->index();
            $table->string('requested_by', 100)->nullable(); // user email or 'system'
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clearance_requests');
    }
};
