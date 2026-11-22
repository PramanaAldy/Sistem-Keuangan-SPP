<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event');                        // created, deleted, updated, login, logout
            $table->string('auditable_type')->nullable();   // App\Models\Transaction
            $table->unsignedBigInteger('auditable_id')->nullable(); // ID record
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('admin_name')->nullable();       // Disimpan statis jika admin dihapus
            $table->string('ip_address', 45)->nullable();
            $table->text('description')->nullable();        // Deskripsi aksi
            $table->json('old_values')->nullable();         // Nilai sebelum diubah
            $table->json('new_values')->nullable();         // Nilai sesudah diubah
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id'], 'idx_audit_target');
            $table->index('admin_id');
            $table->index('event');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
