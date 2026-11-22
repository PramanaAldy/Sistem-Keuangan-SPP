<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // transaction_details: index untuk lookup bulan/tahun per student
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->index(['month', 'year'], 'idx_td_month_year');
        });

        // transactions: composite index untuk filter by student + tahun ajaran
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['student_id', 'academic_year_id'], 'idx_tx_student_year');
            $table->index(['academic_year_id', 'payment_date'], 'idx_tx_year_date');
        });

        // students: index untuk filter aktif per tahun ajaran (paling sering dipakai)
        Schema::table('students', function (Blueprint $table) {
            $table->index(['status', 'academic_year_id'], 'idx_stu_status_year');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropIndex('idx_td_month_year');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_tx_student_year');
            $table->dropIndex('idx_tx_year_date');
        });
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_stu_status_year');
        });
    }
};
