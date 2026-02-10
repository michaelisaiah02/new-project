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
        // 1. Ambil Department ID dari Customer project tsb
        // Kita load relasi customer biar hemat query
        $project->load('customer');

        // Jaga-jaga kalo project gak ada customernya atau customer ga punya dept
        if (!$project->customer || !$project->customer->department_id) {
            return; // Gak bisa kirim notif karena ga tau tujuannya
        }

        $deptId = $project->customer->department_id;
        $customerName = $project->customer->name; // Misal: TMMIN

        // 2. Kumpulkan Nomor WA menggunakan Helper User
        // PIC (Engineering Staff)
        $picNumbers = User::getPIC($deptId)->pluck('whatsapp')->toArray();

        // Non-PIC (Leader, Spv, Management)
        $leaderNumbers = User::getLeader($deptId)->pluck('whatsapp')->toArray();
        $spvNumbers = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();
        $mgmtNumbers = User::getManagement()->pluck('whatsapp')->toArray();

        // Gabungin target Non-PIC jadi satu array
        $otherTargets = array_unique(array_merge($leaderNumbers, $spvNumbers, $mgmtNumbers));


        // 3. SUSUN PESAN (Sesuai Request)

        // Format A: Khusus PIC
        $msgPIC = "[New Project For {$customerName}]\n\n" .
            "Project       : {$project->model}\n" .
            "No Part       : {$project->part_number}\n" .
            "Nama Part     : {$project->part_name}\n" .
            "Suffix        : {$project->suffix}\n\n" .
            "Mohon isikan document yang dibutuhkan dan due date sesuai dengan schedule. Terima kasih.";

        // Format B: Selain PIC (Leader, Spv, Mgmt)
        $msgOthers = "[New Project For {$customerName}]\n\n" .
            "Project       : {$project->model}\n" .
            "No Part       : {$project->part_number}\n" .
            "Nama Part     : {$project->part_name}\n" .
            "Suffix        : {$project->suffix}\n\n" .
            "Mohon diberitahukan kepada PIC Engineering untuk mengisi document yang dibutuhkan dan due date sesuai dengan schedule. Terima kasih.";


        // 4. KIRIM NOTIFIKASI

        // Kirim ke PIC
        if (!empty($picNumbers)) {
            FonnteService::send(implode(',', $picNumbers), $msgPIC);
        }

        // Kirim ke Others
        if (!empty($otherTargets)) {
            FonnteService::send(implode(',', $otherTargets), $msgOthers);
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
