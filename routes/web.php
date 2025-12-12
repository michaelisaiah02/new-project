<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\Engineering\ProjectEngineerController;
use App\Http\Controllers\Marketing\CustomerController;
use App\Http\Controllers\Marketing\ProjectController;
use App\Http\Controllers\Marketing\UserController;
use App\Http\Middleware\CheckDepartmentAccess;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['pong' => true]);
})->name('ping');

Route::middleware('guest')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
    });
});

Route::middleware(['auth', CheckDepartmentAccess::class])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/marketing', [DashboardController::class, 'marketing'])->name('marketing');
    Route::get('/engineering', [DashboardController::class, 'engineering'])->name('engineering');
    Route::get('/management', [DashboardController::class, 'management'])->name('management');

    Route::prefix('document-type')->as('document-type.')->controller(DocumentTypeController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/store', 'store')->name('store');
        Route::post('/update-document-type/{id}', 'update')->name('update');
        Route::delete('/delete-document-type/{id}', 'destroy');
        Route::get('/search', 'search')->name('search');
    });

    Route::prefix('marketing')->as('marketing.')->group(function () {
        Route::prefix('users')->as('users.')->controller(UserController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update-user/{id}', 'update')->name('update');
            Route::delete('/delete-user/{id}', 'destroy');
            Route::get('/search', 'search')->name('search');
        });

        Route::prefix('customers')->as('customers.')->controller(CustomerController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{customer}/edit', 'edit')->name('edit');
            Route::put('/{customer}', 'update')->name('update');
            Route::delete('/delete-customer/{code}', 'destroy');
            Route::get('/{customer}/stages/create', 'createStage')
                ->name('createStage');
            Route::post('/{customer}/stages', 'storeStage')
                ->name('storeStage');
            Route::get('/{customer}/stages/{stageNumber}', 'editStage')
                ->name('editStage');
            Route::put('/{customer}/stages/{stageNumber}', 'saveStage')
                ->name('saveStage');
            Route::delete('/delete-stage/{customer}/{stageNumber}', 'destroyStage')->name('destroyStage');
            Route::get('/search', 'search')->name('search');
        });

        Route::prefix('projects')->as('projects.')->controller(ProjectController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::get('/{project}/edit', 'edit')->name('edit');
            Route::put('/{project}', 'update')->name('update');
        });
    });

    Route::prefix('engineering')->as('engineering.')->group(function () {
        Route::prefix('projects')->as('projects.')->controller(ProjectEngineerController::class)->group(function () {
            Route::get('/{project}', 'new')->name('new');
            Route::post('/{project}', 'saveNew')->name('saveNew');
            Route::get('/{project}/assign-due-date', 'assignDueDates')->name('assignDueDates');
            Route::post('/{project}/assign-due-date', 'saveAssignDueDates')->name('saveAssignDueDates');
            Route::get('/{project}/on-going', 'onGoing')->name('onGoing');
            Route::post('/{project}/on-going', 'updateOnGoing')->name('updateOnGoing');
        });
    });
});
