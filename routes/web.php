<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Public: login / signup / email verification / password reset.
| Inside (auth + verified): dashboard (home), students & face data,
| attendance scanner, reports, history and archive.
|
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'requestForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'sendResetLink'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', function (Request $request) {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('dashboard')
            : view('auth.verify-email');
    })->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('verify-email/resend', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Students: enrollment with face data, profile, edit, delete.
    Route::resource('students', StudentController::class);
    Route::post('students/{student}/archive', [ArchiveController::class, 'store'])
        ->name('students.archive');

    // Attendance: today's sheet, webcam face scanner, manual fallback.
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
    Route::post('attendance/recognize', [AttendanceController::class, 'recognize'])
        ->middleware('throttle:120,1')
        ->name('attendance.recognize');
    Route::post('attendance/manual', [AttendanceController::class, 'storeManual'])->name('attendance.manual');

    // Reports.
    Route::get('reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/yearly', [ReportController::class, 'yearly'])->name('reports.yearly');

    // History.
    Route::get('history', [HistoryController::class, 'index'])->name('history.index');

    // Archive.
    Route::get('archive', [ArchiveController::class, 'index'])->name('archive.index');
    Route::post('archive/{student}/restore', [ArchiveController::class, 'restore'])->name('archive.restore');
});
