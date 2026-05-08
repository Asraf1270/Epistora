<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('user')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.submit');
    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');
});

Route::middleware(['auth'])->group(function () {
    Route::view('/user/dashboard', 'placeholder.writer-dashboard')->name('writer.dashboard');
    Route::view('/user/notifications', 'placeholder.notifications')->name('user.notifications');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::view('/', 'placeholder.admin-dashboard')->name('admin.dashboard');
});
