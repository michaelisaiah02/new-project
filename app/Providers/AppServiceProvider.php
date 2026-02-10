<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\ApprovalStatus;
use App\Models\ProjectDocument;
use App\Observers\ProjectObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\ApprovalStatusObserver;
use App\Observers\ProjectDocumentObserver;

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
