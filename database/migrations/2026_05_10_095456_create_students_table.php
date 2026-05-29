<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reg_no')->unique();
            $table->enum('grade_level', ['7', '8', '9', '10', '11', '12']);
            $table->string('section')->nullable();
            $table->string('program')->nullable();
            $table->enum('clearance_status', ['pending', 'in_progress', 'completed', 'rejected'])->default('pending');
            $table->integer('completed_steps')->default(0);
            $table->integer('total_steps')->default(6); // Library (2), Finance (2), Exams & Records (1), Enrollment/Exam Permit (1)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
