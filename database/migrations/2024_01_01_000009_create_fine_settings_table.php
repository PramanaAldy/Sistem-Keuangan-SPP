<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fine_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->integer('due_date')->default(10); // tanggal jatuh tempo (1-31)
            $table->enum('fine_type', ['flat', 'daily'])->default('flat');
            $table->decimal('amount', 12, 2)->default(0); // flat or per-day amount
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fine_settings');
    }
};
