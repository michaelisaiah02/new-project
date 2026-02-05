<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Project;
use App\Services\FonnteService;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        // Logic: Kirim ke Approver, Checker, Management
        $targets = [
            User::getApproverNumbers(),
            User::getCheckerNumbers(),
            User::getManagementNumbers()
        ];
        // Gabungin nomor biar sekali kirim (kalo Fonnte support multi-target koma)
        $allTargets = implode(',', array_filter($targets));

        $msg = "📢 *Project Baru Dibuat*\n\nPart: {$project->part_name}\nModel: {$project->model}\n\nMohon dicek!";
        FonnteService::send($allTargets, $msg);
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        // POIN 4: Status berubah jadi 'on going'
        if ($project->isDirty('remark') && $project->remark === 'on going') {
            // Logic: Kirim ke Engineering
            // Note: Lo harus punya logic spesifik engineering mana yg dituju.
            // Disini gue tembak ke semua engineering.
            $engNumbers = User::getEngineeringNumbers();

            $msg = "🚀 *Project On Going*\n\nProject {$project->part_name} sekarang statusnya On Going. Silakan diproses engineering.";
            FonnteService::send($engNumbers, $msg);
        }

        // POIN 7: Semua dokumen selesai (Tricky logic)
        // Kita cek manual apakah dokumen project ini udah actual_date semua?
        if ($project->documents()->exists() && $project->documents()->whereNull('actual_date')->count() == 0) {
            // Cek biar ga spam, misal check flag di DB atau pastikan baru aja complete
            // (Simplifikasi: kirim notif)

            $targets = implode(',', [User::getCheckerNumbers(), User::getApproverNumbers(), User::getManagementNumbers()]);
            $msg = "✅ *All Documents Completed*\n\nProject {$project->part_name} semua dokumen sudah lengkap. Siap pindah Masspro!";
            FonnteService::send($targets, $msg);
        }
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        //
    }

    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void
    {
        //
    }

    /**
     * Handle the Project "force deleted" event.
     */
    public function forceDeleted(Project $project): void
    {
        //
    }
}
