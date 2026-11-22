<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('discount_id')->constrained('discounts')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['student_id', 'discount_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_discounts');
    }
};
