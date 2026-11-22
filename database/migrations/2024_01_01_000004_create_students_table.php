<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique()->nullable(); // nomor induk siswa
            $table->string('full_name');
            $table->date('birth_date');
            $table->string('birth_place')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->foreignId('class_id')->constrained('classes')->onDelete('restrict');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('restrict');
            $table->enum('status', ['active', 'inactive', 'alumni'])->default('active');
            $table->boolean('is_free_spp')->default(false);  // siswa free SPP
            $table->boolean('is_free_fine')->default(false); // bebas denda
            $table->string('parent_name')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('address')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
