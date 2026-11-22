<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\SppSettingController;
use App\Http\Controllers\Admin\FineSettingController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\TutorialController;
use App\Http\Controllers\Admin\DebtLetterController;
use App\Http\Controllers\Admin\AuditLogController;

// Redirect root to admin login
Route::redirect('/', '/login');

// ─── Admin Panel ─────────────────────────────────────────────────────────────
Route::get('/login', [AdminLoginController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

Route::middleware('admin.auth')->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // Tahun Ajaran
    Route::resource('academic-years', AcademicYearController::class)->parameters(['academic-years' => 'academicYear']);
    Route::post('academic-years/{id}/set-active', [AcademicYearController::class, 'setActive'])->name('academic-years.set-active');

    // Kelas
    Route::resource('classes', SchoolClassController::class);

    // Siswa — rute statis harus di atas resource agar tidak tertangkap {student}
    Route::get('students/export',   [StudentController::class, 'export'])->name('students.export');
    Route::get('students/template', [StudentController::class, 'downloadTemplate'])->name('students.template');
    Route::post('students/import',  [StudentController::class, 'import'])->name('students.import');
    Route::post('students/bulk-action', [StudentController::class, 'bulkAction'])->name('students.bulk-action');
    Route::resource('students', StudentController::class);
    Route::post('students/{student}/set-sibling',          [StudentController::class, 'setSibling'])->name('students.set-sibling');
    Route::delete('students/{student}/remove-sibling/{sibling}', [StudentController::class, 'removeSibling'])->name('students.remove-sibling');
    Route::post('students/{student}/set-discount',         [StudentController::class, 'setDiscount'])->name('students.set-discount');
    Route::delete('students/{student}/remove-discount/{discount}', [StudentController::class, 'removeDiscount'])->name('students.remove-discount');

    // Diskon
    Route::resource('discounts', DiscountController::class);

    // SPP Setting
    Route::resource('spp-settings', SppSettingController::class)->parameters(['spp-settings' => 'sppSetting']);

    // Fine Setting
    Route::resource('fine-settings', FineSettingController::class)->parameters(['fine-settings' => 'fineSetting']);

    // Transaksi
    Route::get('transactions/calculate', [TransactionController::class, 'calculate'])->name('transactions.calculate');
    Route::resource('transactions', TransactionController::class);
    Route::get('transactions/{transaction}/receipt', [TransactionController::class, 'printReceipt'])->name('transactions.receipt');
    Route::delete('transaction-details/{detail}', [TransactionController::class, 'destroyDetail'])->name('transactions.detail.destroy');
    
    // Setor Bank
    Route::get('bank-deposits/print', [App\Http\Controllers\Admin\BankDepositController::class, 'print'])->name('bank-deposits.print');
    Route::resource('bank-deposits', App\Http\Controllers\Admin\BankDepositController::class)->parameters(['bank-deposits' => 'bankDeposit'])->only(['index', 'create', 'store', 'destroy']);

    // Laporan
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/recap', [ReportController::class, 'recap'])->name('reports.recap');
    Route::get('reports/yearly', [ReportController::class, 'yearly'])->name('reports.yearly');
    Route::get('reports/arrears', [ReportController::class, 'arrears'])->name('reports.arrears');
    Route::get('reports/control-file', [ReportController::class, 'controlFile'])->name('reports.control-file');
    Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');

    // Pengingat
    Route::get('reminders', [ReminderController::class, 'index'])->name('reminders.index');
    Route::get('reminders/{student}/whatsapp', [ReminderController::class, 'whatsapp'])->name('reminders.whatsapp');

    // Surat Tunggakan
    Route::get('debt-letters', [DebtLetterController::class, 'index'])->name('debt-letters.index');
    Route::get('debt-letters/{student}/download-pdf', [DebtLetterController::class, 'downloadPdf'])->name('debt-letters.download-pdf');
    Route::get('debt-letters/{student}', [DebtLetterController::class, 'show'])->name('debt-letters.show');

    // User Admin
    Route::resource('admin-users', AdminUserController::class)->parameters(['admin-users' => 'adminUser']);

    // Tutorial
    Route::get('tutorial', [TutorialController::class, 'index'])->name('tutorial.index');

    // Ganti Password
    Route::get('change-password', [AdminUserController::class, 'showChangePassword'])->name('change-password.show');
    Route::put('change-password', [AdminUserController::class, 'changePassword'])->name('change-password.update');

    // Audit Log
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/export', [AuditLogController::class, 'export'])->name('audit-logs.export');

    // Cek Saudara
    Route::get('sibling-check', [\App\Http\Controllers\Admin\SiblingCheckController::class, 'index'])->name('sibling-check.index');
});
