<?php

namespace App\Providers;

use App\Models\ApprovalStatus;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Observers\ApprovalStatusObserver;
use App\Observers\ProjectDocumentObserver;
use App\Observers\ProjectObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Project::observe(ProjectObserver::class);
        ProjectDocument::observe(ProjectDocumentObserver::class);
        ApprovalStatus::observe(ApprovalStatusObserver::class);
    }
}
