<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WhatsappWebhookController;
use App\Http\Controllers\PesanTunggalController;
use App\Http\Controllers\BulkingController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

//dashboard route
Route::get('/dashboard', [DashboardController::class, 'index'])->name("dashboard");
Route::get('/dashboard/status', [DashboardController::class, 'status'])->name('dashboard.status');
Route::get('/dashboard/refresh-qr', [DashboardController::class, 'refreshQr'])->name('dashboard.refresh');

//pesan tunggal route
Route::get('/pesan-tunggal', [PesanTunggalController::class, 'index'])->name("pesan-tunggal.index");
Route::post('/pesan-tunggal', [PesanTunggalController::class, 'store'])->name("pesan-tunggal.store");
Route::get('/pesan-tunggal/log', [PesanTunggalController::class, 'log'])->name("pesan-tunggal.log");
Route::get('/pesan-tunggal/check-number', [PesanTunggalController::class, 'checkNumber'])->name("pesan-tunggal.check");

//bulking route
Route::get('/bulking', [BulkingController::class, 'index'])->name("bulking.index");
Route::post('/bulking', [BulkingController::class, 'store'])->name("bulking.store");
Route::get('/bulking/log', [BulkingController::class, 'log'])->name("bulking.log");
Route::get('/bulking/check-number', [BulkingController::class, 'checkNumber'])->name("bulking.check");
Route::get('/bulking/{campaign}', [BulkingController::class, 'show'])->name("bulking.show");
Route::post('/bulking/{campaign}/pause', [BulkingController::class, 'pause'])->name("bulking.pause");
Route::post('/bulking/{campaign}/resume', [BulkingController::class, 'resume'])->name("bulking.resume");
