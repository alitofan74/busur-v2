<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WhatsappWebhookController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});


Route::get('/dashboard', [DashboardController::class, 'index'])->name("dashboard");
Route::get('/dashboard/status', [DashboardController::class, 'status'])->name('dashboard.status');
Route::get('/dashboard/refresh-qr', [DashboardController::class, 'refreshQr'])->name('dashboard.refresh');



