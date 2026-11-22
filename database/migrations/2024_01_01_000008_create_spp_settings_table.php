<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('spp_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['class_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spp_settings');
    }
};
