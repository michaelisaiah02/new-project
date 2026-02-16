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
    public function created(Project $project)
    {
        // 1. Load relasi customer biar bisa dapet nama dan department_id-nya
        $project->load('customer');

        // Jaga-jaga kalau datanya bolong (ga ada customer / department)
        if (!$project->customer || !$project->customer->department_id) {
            return;
        }

        $deptId = $project->customer->department_id;
        $customerName = $project->customer->name;

        // 2. Kumpulin nomor WA target (Leader, Supervisor, Management)
        $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();
        $supervisors = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();
        $managements = User::getManagement()->pluck('whatsapp')->toArray();

        // Gabungin semua target jadi satu array, dan hilangkan duplikat pake array_unique
        $targets = array_unique(array_merge($leaders, $supervisors, $managements));

        // 3. Susun Pesan sesuai format (Sat-set langsung mapping variabel)
        if (!empty($targets)) {
            $msg = "New Project For {$customerName} Project {$project->model}\n" .
                "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                "Target Mass Production : {$project->masspro_target}\n\n" .
                "Mohon diberitahukan kepada PIC untuk segera membuat schedule document.\n" .
                "Terima kasih.";

            // 4. Tembak Fonnte! 🚀
            FonnteService::send(implode(',', $targets), $msg);
        }
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
