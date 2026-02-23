<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\User;
use App\Services\BroadcastService;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project)
    {
        // 1. Load relasi customer biar bisa dapet nama dan department_id-nya
        $project->load('customer');

        // Jaga-jaga kalau datanya bolong (ga ada customer / department)
        if (! $project->customer || ! $project->customer->department_id) {
            return;
        }

        $deptId = $project->customer->department_id;
        $customerName = $project->customer->name;

        // 2. Kumpulin nomor WA target (Leader, Supervisor, Management)
        $leaders = User::getLeader($deptId);
        $supervisors = User::getSupervisor($deptId);
        $managements = User::getManagement();

        // Gabungin semua target jadi satu array, dan hilangkan duplikat pake array_unique
        $targets = collect($leaders)->merge($supervisors)->merge($managements)
            ->unique('id');

        // 3. Susun Pesan sesuai format (Sat-set langsung mapping variabel)
        if (! empty($targets)) {
            $msg = "New Project For {$customerName} Project {$project->model}\n".
                "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n".
                "Target Mass Production : {$project->masspro_target}\n\n".
                "Mohon diberitahukan kepada PIC untuk segera membuat schedule document.\n".
                'Terima kasih.';

            // 4. Tembak Fonnte! 🚀
            BroadcastService::send($targets, $msg, "Notification Project $project->model");
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
