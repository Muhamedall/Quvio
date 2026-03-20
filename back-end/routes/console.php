<?php

// ============================================================
// routes/console.php  —  Laravel 13
//
// WHAT CHANGED FROM OLDER LARAVEL:
//
//   OLD WAY (Laravel 10 and below):
//     app/Console/Kernel.php
//     protected function schedule(Schedule $schedule): void {
//         $schedule->command('invoices:mark-overdue')->dailyAt('08:00');
//     }
//
//   NEW WAY (Laravel 13):
//     Kernel.php is GONE.
//     All scheduling lives here in routes/console.php
//     Using the Schedule facade directly.
//
// HOW TO ACTIVATE ON SERVER:
//   Add this cron job to your server (runs every minute):
//   * * * * * cd /var/www/quvio-api && php artisan schedule:run >> /dev/null 2>&1
//
//   Laravel's scheduler reads this file and runs jobs at the right time.
//
// HOW TO TEST LOCALLY:
//   php artisan schedule:run         → run all due jobs now
//   php artisan invoices:mark-overdue → run the command directly
// ============================================================

use Illuminate\Support\Facades\Schedule;

// Mark unpaid invoices as overdue every day at 08:00
// This runs the MarkOverdueInvoices artisan command automatically
Schedule::command('app:mark-overdue-invoices')
    ->dailyAt('08:00')
    ->withoutOverlapping()    // prevent running twice if previous run is still going
    ->onOneServer()           // if multiple servers, only run on one
    ->appendOutputTo(storage_path('logs/scheduler.log')); // log output