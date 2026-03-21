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
        Schema::create('final_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->decimal('total_student_grades', 6, 2);
            $table->decimal('average_grade', 5, 2)->nullable();
            $table->string('final_result');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'academic_year_id'], 'student_year_unique_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_results');
    }
};
