<?php
/**
 * Script helper — Jalankan via browser: http://sistemkeuangansppuniversalschool.test/run_migrate.php
 * HAPUS FILE INI SETELAH DIGUNAKAN!
 */

// Load Laravel bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$results = [];

try {
    // --- Index 1: transaction_details (month, year) ---
    if (!indexExists('transaction_details', 'idx_td_month_year')) {
        DB::statement('ALTER TABLE transaction_details ADD INDEX idx_td_month_year (month, year)');
        $results[] = ['ok', 'Index idx_td_month_year pada transaction_details → DIBUAT'];
    } else {
        $results[] = ['skip', 'Index idx_td_month_year sudah ada'];
    }

    // --- Index 2: transactions (student_id, academic_year_id) ---
    if (!indexExists('transactions', 'idx_tx_student_year')) {
        DB::statement('ALTER TABLE transactions ADD INDEX idx_tx_student_year (student_id, academic_year_id)');
        $results[] = ['ok', 'Index idx_tx_student_year pada transactions → DIBUAT'];
    } else {
        $results[] = ['skip', 'Index idx_tx_student_year sudah ada'];
    }

    // --- Index 3: transactions (academic_year_id, payment_date) ---
    if (!indexExists('transactions', 'idx_tx_year_date')) {
        DB::statement('ALTER TABLE transactions ADD INDEX idx_tx_year_date (academic_year_id, payment_date)');
        $results[] = ['ok', 'Index idx_tx_year_date pada transactions → DIBUAT'];
    } else {
        $results[] = ['skip', 'Index idx_tx_year_date sudah ada'];
    }

    // --- Index 4: students (status, academic_year_id) ---
    if (!indexExists('students', 'idx_stu_status_year')) {
        DB::statement('ALTER TABLE students ADD INDEX idx_stu_status_year (status, academic_year_id)');
        $results[] = ['ok', 'Index idx_stu_status_year pada students → DIBUAT'];
    } else {
        $results[] = ['skip', 'Index idx_stu_status_year sudah ada'];
    }

    // Catat di tabel migrations
    $ran = DB::table('migrations')->pluck('migration')->toArray();
    if (!in_array('2026_03_12_000001_add_performance_indexes', $ran)) {
        DB::table('migrations')->insert([
            'migration' => '2026_03_12_000001_add_performance_indexes',
            'batch'     => (DB::table('migrations')->max('batch') ?? 0) + 1,
        ]);
        $results[] = ['ok', 'Migration dicatat ke tabel migrations'];
    }

} catch (\Exception $e) {
    $results[] = ['error', 'ERROR: ' . $e->getMessage()];
}

function indexExists(string $table, string $indexName): bool
{
    $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
    return count($indexes) > 0;
}

// Output
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>DB Migrate Helper</title>';
echo '<style>body{font-family:monospace;padding:30px;background:#0f1117;color:#f1f5f9} .ok{color:#34d399} .skip{color:#fbbf24} .error{color:#f87171}</style></head><body>';
echo '<h2 style="color:#f59e0b">🗄️ Database Index Migration</h2>';
foreach ($results as [$type, $msg]) {
    $icon = $type === 'ok' ? '✅' : ($type === 'skip' ? '⏭️' : '❌');
    echo "<p class=\"{$type}\">{$icon} {$msg}</p>";
}
echo '<hr style="border-color:#2d3348;margin:20px 0">';
echo '<p style="color:#64748b;font-size:12px">⚠️ <strong>HAPUS FILE INI</strong> setelah selesai: <code>public/run_migrate.php</code> atau <code>run_migrate.php</code></p>';
echo '</body></html>';
