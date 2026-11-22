<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['PG', 'TK', 'SD', 'SMP']);
            $table->string('name'); // e.g. "TK A", "1A", "2B", "7"
            $table->string('display_name'); // e.g. "Kelas 1A", "TK A"
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
