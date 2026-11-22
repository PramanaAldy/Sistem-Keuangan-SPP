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
        Schema::create('bank_deposits', function (Blueprint $table) {
            $table->id();
            $table->date('deposit_date');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('bank_1_percentage', 5, 2)->default(50);
            $table->decimal('bank_1_amount', 15, 2);
            $table->decimal('bank_2_percentage', 5, 2)->default(50);
            $table->decimal('bank_2_amount', 15, 2);
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_deposits');
    }
};
