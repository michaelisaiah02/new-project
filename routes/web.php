<?php

use App\Http\Middleware\CheckLogin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Marketing\UserController;
use App\Http\Controllers\Marketing\CustomerController;
use App\Http\Controllers\Marketing\NewProjectController;

Route::get('/ping', function () {
    return response()->json(['pong' => true]);
})->name('ping');

Route::get('/', function () {
    return redirect()->route('marketing');
});

Route::middleware('guest')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/marketing', [DashboardController::class, 'marketing'])->name('marketing');
    Route::get('/engineering', [DashboardController::class, 'engineering'])->name('engineering');
    Route::get('/management', [DashboardController::class, 'management'])->name('management');
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
        Route::get('/create', 'create')->name('marketing.customers.create');
        Route::get('/customers/{customer}/stages/create', [CustomerController::class, 'createStage'])
            ->name('marketing.customers.createStage');
        Route::post('/customers/{customer}/stages', [CustomerController::class, 'storeStage'])
            ->name('marketing.customers.storeStage');
        Route::post('/store', 'store')->name('marketing.customers.store');
        Route::delete('/delete-customer/{code}', 'destroy');
        Route::get('/search', 'search')->name('marketing.customers.search');
    });

    Route::prefix('marketing/new-report')->controller(NewProjectController::class)->group(function () {
        Route::get('/', 'index')->name('marketing.new_projects.index');
        Route::get('/create', 'create')->name('marketing.new_projects.create');
        Route::post('/store', 'store')->name('marketing.new_projects.store');
        Route::get('/{newProject}', 'show')->name('marketing.new_projects.show');
        Route::get('/{newProject}/edit', 'edit')->name('marketing.new_projects.edit');
        Route::put('/{newProject}', 'update')->name('marketing.new_projects.update');
        Route::delete('/{newProject}', 'destroy')->name('marketing.new_projects.destroy');
    });
});
