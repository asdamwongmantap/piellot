<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PicAccessController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Peta rute PIELLOT (Laravel)
|--------------------------------------------------------------------------
| Dibandingkan versi Next.js aslinya:
| - app/api/bootstrap/route.ts (GET)  -> DashboardController + query per halaman
| - app/api/action/route.ts (POST)    -> masing-masing controller di bawah,
|                                        1 action lama = 1 route method baru.
| - app/chatgpt-auth.ts                -> AuthController (login/register biasa)
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::get('/lupa-password', [AuthController::class, 'showForgotPassword'])->name('password.request')->middleware('guest');
Route::post('/lupa-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {

    // PIC yang belum punya perusahaan wajib mendaftar dulu.
    Route::get('/daftar-perusahaan', [CompanyController::class, 'create'])->name('company.create');
    Route::post('/daftar-perusahaan', [CompanyController::class, 'store'])->name('company.store');
    Route::get('/menunggu-persetujuan', [CompanyController::class, 'waiting'])->name('account.waiting');

    // Semua rute di bawah ini butuh akun yang sudah ACTIVE (admin atau PIC aktif).
    Route::middleware('active')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/jadwal', [ScheduleController::class, 'index'])->name('schedule.index');

        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{booking}', [InvoiceController::class, 'show'])->name('invoices.show');

        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');

        // Khusus PIC.
        Route::middleware('pic')->group(function () {
            Route::get('/bookings-baru', [BookingController::class, 'create'])->name('bookings.create');
            Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

            Route::get('/tim', [TeamController::class, 'index'])->name('team.index');
            Route::post('/admin-requests', [AdminRequestController::class, 'store'])->name('admin-requests.store');

            Route::get('/akun', [AccountController::class, 'show'])->name('account.show');
            Route::put('/akun/password', [AccountController::class, 'updatePassword'])->name('account.password');
        });

        // Khusus Admin Fleet.
        Route::middleware('admin')->group(function () {
            Route::get('/armada', [VehicleController::class, 'index'])->name('vehicles.index');
            Route::post('/armada', [VehicleController::class, 'store'])->name('vehicles.store');
            Route::post('/armada/{vehicle}/toggle', [VehicleController::class, 'toggle'])->name('vehicles.toggle');
            Route::delete('/armada/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

            Route::get('/approvals', [CompanyController::class, 'pendingApprovals'])->name('companies.pending');
            Route::post('/companies/{company}/status', [CompanyController::class, 'setStatus'])->name('companies.status');
            Route::post('/admin-requests/{adminRequest}/status', [AdminRequestController::class, 'setStatus'])->name('admin-requests.status');

            Route::post('/bookings/{booking}/status', [BookingController::class, 'setStatus'])->name('bookings.status');
            Route::post('/invoices/{booking}', [InvoiceController::class, 'update'])->name('invoices.update');
            Route::post('/invoices/{booking}/paid', [InvoiceController::class, 'markPaid'])->name('invoices.paid');

            Route::get('/akses-pic', [PicAccessController::class, 'index'])->name('access.index');

            Route::get('/export', [ExportController::class, 'download'])->name('export.download');

            Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');
        });
    });
});
