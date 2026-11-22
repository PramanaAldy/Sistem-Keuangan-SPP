<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('student_id')->constrained('students')->onDelete('restrict');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('restrict');
            $table->foreignId('admin_id')->constrained('admins')->onDelete('restrict');
            $table->date('payment_date');
            $table->decimal('spp_amount', 12, 2)->default(0);
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->enum('spp_method', ['cash', 'transfer', 'debit'])->default('cash');
            $table->enum('fine_method', ['cash', 'transfer', 'debit'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
