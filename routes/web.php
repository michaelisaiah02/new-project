<?php

use App\Http\Middleware\CheckLogin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Marketing\CustomerController;
use App\Http\Controllers\Marketing\UserController;

Route::get('/ping', function () {
    return response()->json(['pong' => true]);
})->name('ping');

Route::middleware('guest')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/main-menu', [DashboardController::class, 'mainMenu'])->name('main-menu');

    Route::prefix('marketing/users')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('marketing.users.index');
        Route::post('/store', 'store')->name('marketing.users.store');
        Route::post('/update-user/{id}', 'update')->name('marketing.users.update');
        Route::delete('/delete-user/{id}', 'destroy');
        Route::get('/search', 'search')->name('marketing.users.search');
    });

    Route::prefix('marketing/customers')->controller(CustomerController::class)->group(function () {
        Route::get('/', 'index')->name('marketing.customers.index');
        Route::post('/store', 'store')->name('marketing.customers.store');
        Route::post('/update-customer/{code}', 'update')->name('marketing.customers.update');
        Route::delete('/delete-customer/{code}', 'destroy');
        Route::get('/search', 'search')->name('marketing.customers.search');
    });
});
