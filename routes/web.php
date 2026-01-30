<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\Engineering\ProjectDocumentController;
use App\Http\Controllers\Engineering\ProjectEngineerController;
use App\Http\Controllers\KPIController;
use App\Http\Controllers\Marketing\CustomerController;
use App\Http\Controllers\Marketing\ProjectController;
use App\Http\Controllers\Marketing\UserController;
use App\Http\Controllers\MassproController;
use App\Http\Middleware\CheckDepartmentAccess;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['pong' => true]);
})->name('ping');
Route::get('/qr', [ProjectDocumentController::class, 'show'])->name('qr');

Route::middleware('guest')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/marketing', [DashboardController::class, 'marketing'])->name('marketing');
    Route::get('/engineering', [DashboardController::class, 'engineering'])->name('engineering');
    Route::get('/management', [DashboardController::class, 'management'])->name('management');

    Route::prefix('document-type')->as('document-type.')->controller(DocumentTypeController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/store', 'store')->name('store');
        Route::post('/update/{code}', 'update')->name('update');
        Route::delete('/delete/{code}', 'destroy');
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
            Route::post('/{project}/new', 'saveNew')->name('saveNew');
            Route::get('/{project}/assign-due-date', 'assignDueDates')->name('assignDueDates');
            Route::post('/{project}/save-due-date', 'saveDueDates')->name('saveDueDates');
            Route::post('/{project}/update-due-dates', 'updateDueDates')->name('updateDueDates');
            Route::post('/approval', 'approval')->name('approval');
            Route::post('/{project}/update-to-on-going', 'updateToOnGoing')->name('updateToOnGoing');
            Route::get('/{project}/on-going', 'onGoing')->name('onGoing');
            Route::post('/{project}/on-going', 'updateOnGoing')->name('updateOnGoing');
            Route::post('/{project}/checked/ongoing', 'checkedOngoing')->name('checkedOngoing');
            Route::post('/{project}/approved/ongoing', 'approvedOngoing')->name('approvedOngoing');
            Route::post('/{project}/cancel', 'cancel')->name('cancel');
        });
        Route::prefix('project-documents')->as('project-documents.')->controller(ProjectDocumentController::class)->group(function () {
            Route::get('/{projectDocument}/view', 'view')->name('view');
            Route::post('/{projectDocument}/upload', 'upload')->name('upload');
            Route::post('/{projectDocument}/remark', 'updateRemark')->name('remark');
            Route::post('/{projectDocument}/checked', 'checked')->name('checked');
            Route::post('/{projectDocument}/approved', 'approved')->name('approved');
        });
    });

    Route::prefix('masspro')->as('masspro.')->controller(MassproController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/view/{project}', 'view')->name('view');
        Route::get('{projectDocument}/view', 'document')->name('document');
        Route::get('/filter-options', 'getFilterOptions')->name('filterOptions');
        Route::get('/api/get-models', 'getModels')->name('api.models');
        Route::get('/api/get-parts', 'getParts')->name('api.parts');
        Route::get('/api/get-minor-changes', 'getMinorChanges')->name('api.minorChanges');
        Route::get('/api/get-suffixes', 'getSuffixes')->name('api.suffixes');
    });
});
Route::prefix('kpi')->as('kpi.')->controller(KPIController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/api/get-models', 'getModels')->name('api.models');
    Route::get('/api/get-parts', 'getParts')->name('api.parts');
    Route::get('/api/get-variants', 'getVariants')->name('api.variants');
});
