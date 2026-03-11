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
        Schema::create('errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('field_name');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('reason')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('createdBy')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('errors');
    }
};
