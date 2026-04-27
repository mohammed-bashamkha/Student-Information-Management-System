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
        Schema::create('transfers_admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('type', ['transfer', 'admission']);
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('from_school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->text('from_external_school_name')->nullable();
            $table->foreignId('to_school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->date('request_date');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('based_on')->nullable();
            $table->date('approval_date')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers_admissions');
    }
};
