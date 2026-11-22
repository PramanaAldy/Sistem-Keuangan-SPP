<?php

use App\Console\Commands\BackupDatabase;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Backup otomatis setiap hari jam 01:00 dini hari ──────────────────────
Schedule::command('db:backup')->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Backup database GAGAL pada ' . now());
    });

// ── Bersihkan view cache setiap Minggu jam 02:00 ─────────────────────────
Schedule::command('view:clear')->weeklyOn(0, '02:00');
