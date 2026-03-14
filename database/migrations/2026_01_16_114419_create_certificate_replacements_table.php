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
        Schema::create('certificate_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('class_id')->constrained('school_classes');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->string('certificate_type');
            $table->text('notes')->nullable();
            $table->date('request_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_replacements');
    }
};
