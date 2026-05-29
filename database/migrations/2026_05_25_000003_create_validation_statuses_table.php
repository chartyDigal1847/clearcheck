<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clearance_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('previous_status', [
                'pending', 'validating', 'partially_cleared', 'cleared', 'disputed', 'expired',
            ])->nullable();
            $table->enum('new_status', [
                'pending', 'validating', 'partially_cleared', 'cleared', 'disputed', 'expired',
            ]);
            $table->string('triggered_by', 100)->nullable(); // system | user | event
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['clearance_record_id', 'created_at']);
            $table->index(['student_id', 'new_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_statuses');
    }
};
