<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_confirmations', function (Blueprint $table) {
            $table->text('months_json')->nullable()->after('note');   // JSON array of {month, year}
            $table->decimal('total_amount', 12, 2)->default(0)->after('months_json');
        });
    }

    public function down(): void
    {
        Schema::table('payment_confirmations', function (Blueprint $table) {
            $table->dropColumn(['months_json', 'total_amount']);
        });
    }
};
