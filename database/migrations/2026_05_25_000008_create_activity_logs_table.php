<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor', 150)->nullable()->index();   // email or 'system'
            $table->string('actor_role', 50)->nullable();
            $table->string('action', 150)->index();
            $table->string('subject_type', 100)->nullable();     // model class
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->enum('level', ['info', 'warning', 'critical'])->default('info')->index();
            $table->timestamp('occurred_at')->useCurrent()->index();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['actor', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
