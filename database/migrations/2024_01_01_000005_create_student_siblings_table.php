<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_siblings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('sibling_id')->constrained('students')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['student_id', 'sibling_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_siblings');
    }
};
