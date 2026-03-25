<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\User;
use App\Services\BroadcastService;
use Carbon\Carbon;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project)
    {
        // 1. Load data
        $project->load('customer');
        $customerName = $project->customer ? $project->customer->name : '-';
        $targetMasspro = Carbon::parse($project->target_masspro)->translatedFormat('d F Y');

        // 2. PESAN KHUSUS WA (Format lama yang lo pake sebelumnya)
        $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
            "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
            "Target Mass Production : {$project->masspro_target}\n\n" .
            "Mohon diberitahukan kepada PIC untuk segera membuat schedule document.\n" .
            'Terima kasih.';

        // 3. PESAN KHUSUS EMAIL (Format baru dari klien)
        $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
            "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
            "Target Mass Production : {$targetMasspro}\n\n" .
            "Mohon diberitahukan kepada PIC untuk segera membuat schedule new project di *aplikasi new project CAR*.\n" .
            "Terima kasih.";

        $deptId = $project->customer->department_id;
        $leaders = User::getLeader($deptId);
        $supervisors = User::getSupervisor($deptId);
        $managements = User::getManagement();
        // 4. Ambil target TO dan CC
        $targetTo = $leaders;
        $targetCc = collect($supervisors)->merge($managements)->unique('id');

        // 5. Eksekusi JUTSU DUAL-CORE! 💥
        if ($targetTo->isNotEmpty() || $targetCc->isNotEmpty()) {
            BroadcastService::sendBatch(
                $targetTo,
                $targetCc,
                $msgWa,      // Masukin pesan WA
                $msgEmail,   // Masukin pesan Email
                "New Project Notification: {$project->model}"
            );
        }
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        //
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
