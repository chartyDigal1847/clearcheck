<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action', 100)->index();   // validate_request | module_response | status_change | etc.
            $table->string('module_key', 50)->nullable()->index();
            $table->string('actor', 150)->nullable();  // email or 'system'
            $table->string('ip_address', 45)->nullable();
            $table->json('context')->nullable();       // arbitrary metadata
            $table->enum('level', ['info', 'warning', 'error'])->default('info')->index();
            $table->string('correlation_id', 64)->nullable()->index();
            $table->timestamp('logged_at')->useCurrent()->index();

            $table->index(['student_id', 'logged_at']);
            $table->index(['action', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_logs');
    }
};
