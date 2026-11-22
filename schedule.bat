@echo off
:: ============================================================
:: schedule.bat - Laravel Scheduler untuk SPP Universal School
:: Jalankan file ini via Windows Task Scheduler setiap 1 menit
:: ============================================================

set PHP_PATH=d:\laragon\bin\php\php8.3.14\php.exe
set PROJECT_PATH=d:\laragon\www\SistemKeuanganSppUniversalSchool\artisan

"%PHP_PATH%" "%PROJECT_PATH%" schedule:run >> d:\laragon\www\SistemKeuanganSppUniversalSchool\storage\logs\scheduler.log 2>&1
