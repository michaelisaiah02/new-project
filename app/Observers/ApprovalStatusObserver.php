<?php

namespace App\Observers;

use App\Models\ApprovalStatus;
use App\Models\User;
use App\Services\BroadcastService;
use Carbon\Carbon;

class ApprovalStatusObserver
{
    /**
     * Handle the ApprovalStatus "created" event.
     */
    public function created(ApprovalStatus $approvalStatus): void
    {
        //
    }

    /**
     * Handle the ApprovalStatus "updated" event.
     */
    public function updated(ApprovalStatus $status)
    {
        // Load relasi project dan customer sekalian biar datanya komplit
        $status->load(['project.customer']);
        $project = $status->project;

        // Validasi anti-error club
        if (! $project || ! $project->customer || ! $project->customer->department_id) {
            return;
        }

        $deptId = $project->customer->department_id;
        $customerName = $project->customer->name;

        // =========================================================
        // KONDISI: LEADER SUDAH CHECK SCHEDULE
        // =========================================================
        // Cek apakah kolom checked_date baru aja berubah dan ada isinya
        if ($status->isDirty('checked_date') && ! empty($status->checked_date)) {

            // Cari Supervisor dari department yang terkait
            $supervisors = User::getSupervisor($deptId);

            // Kalau dapet nomor WA-nya, gaskan kirim!
            if (! empty($supervisors)) {
                $msg = "New Project For {$customerName} Project {$project->model}\n".
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n".
                    "Target Mass Production : {$project->masspro_target}\n".
                    "Schedule sudah di-checked.\n\n".
                    "Mohon segera di-*approve* schedule yang telah dibuat.\n".
                    'Terima kasih.';

                BroadcastService::send($supervisors, $msg, "Notification Project $project->model");
            }
        }

        // =========================================================
        // KONDISI: SUPERVISOR SUDAH APPROVE SCHEDULE
        // =========================================================
        // Cek apakah kolom approved_date baru aja berubah dan ada isinya
        if ($status->isDirty('approved_date') && ! empty($status->approved_date)) {

            // Cari orang-orang Management (Gak perlu pake $deptId karena management biasanya global)
            $managements = User::getManagement();

            // Kalau dapet nomor WA-nya, gaskan kirim!
            if (! empty($managements)) {
                $msg = "New Project For {$customerName} Project {$project->model}\n".
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n".
                    "Target Mass Production : {$project->masspro_target}\n".
                    "Schedule sudah di-approved.\n\n".
                    "Mohon segera *disetujui* schedule yang telah dibuat, agar bisa dimulai new project ini.\n".
                    'Terima kasih.';

                BroadcastService::send($managements, $msg, "Notification Project $project->model");
            }
        }

        // =========================================================
        // KONDISI: MANAGEMENT SUDAH APPROVE (RESMI ON-GOING PROJECT)
        // =========================================================
        // Cek apakah kolom management_approved_date baru aja berubah dan ada isinya
        if ($status->isDirty('management_approved_date') && ! empty($status->management_approved_date)) {

            // Cari Leader dan Supervisor dari department yang terkait
            $leaders = User::getLeader($deptId);
            $supervisors = User::getSupervisor($deptId);

            // Gabungin target (biar efisien sekali jalan)
            $targets = collect($leaders)->merge($supervisors)->unique('id');

            // Kalau ada nomor WA-nya, langsung blast!
            if (! empty($targets)) {
                $msg = "New Project For {$customerName} Project {$project->model}\n".
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n".
                    "Target Mass Production : {$project->masspro_target}\n".
                    "*SUDAH DIMULAI (ON-GOING PROJECT)*\n\n".
                    "Mohon dilakukan monitoring hingga masspro.\n".
                    'Terima kasih.';

                BroadcastService::send($targets, $msg, "Notification Project $project->model");
            }
        }

        // =========================================================
        // KONDISI: LEADER SUDAH CHECK ON-GOING PROJECT (SEMUA DOC LENGKAP)
        // =========================================================
        // Cek apakah kolom ongoing_checked_date baru aja berubah dan ada isinya
        if ($status->isDirty('ongoing_checked_date') && ! empty($status->ongoing_checked_date)) {

            // Hitung Target Besok (tanggal checked + 1 hari)
            $targetDate = Carbon::parse($status->ongoing_checked_date)->addDay()->format('d F Y');

            // Cari Supervisor dari department yang terkait
            $supervisors = User::getSupervisor($deptId);

            // Kalau dapet nomor WA-nya, langsung blast!
            if (! empty($supervisors)) {
                $msg = "New Project For {$customerName} Project {$project->model}\n".
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n".
                    "Semua document sudah di-check.\n\n".
                    "Mohon di-approve agar bisa segera masspro.\n".
                    "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n".
                    'Terima kasih.';

                BroadcastService::send($supervisors, $msg, "Notification Project $project->model");
            }
        }

        // =========================================================
        // KONDISI: SUPERVISOR SUDAH APPROVE ON-GOING PROJECT
        // =========================================================
        // Cek apakah kolom ongoing_approved_date baru aja berubah dan ada isinya
        if ($status->isDirty('ongoing_approved_date') && ! empty($status->ongoing_approved_date)) {

            // Hitung Target Besok (tanggal approved + 1 hari)
            $targetDate = Carbon::parse($status->ongoing_approved_date)->addDay()->format('d F Y');

            // Cari user Management (Global, gak pake $deptId)
            $managements = User::getManagement();

            // Kalau dapet nomor WA-nya, langsung gaskan!
            if (! empty($managements)) {
                $msg = "New Project For {$customerName} Project {$project->model}\n".
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n".
                    "Semua document sudah di-approve oleh supervisor.\n\n".
                    "Mohon di-approve by management agar bisa segera masspro.\n".
                    "Target besok, tgl {$targetDate} sudah dikerjakan.\n".
                    'Terima kasih.';

                BroadcastService::send($managements, $msg, "Notification Project $project->model");
            }
        }

        // =========================================================
        // KONDISI: MANAGEMENT SUDAH APPROVE ON-GOING (FINAL PROJECT SELESAI)
        // =========================================================
        // Cek apakah kolom ongoing_management_approved_date baru aja berubah dan ada isinya
        if ($status->isDirty('ongoing_management_approved_date') && ! empty($status->ongoing_management_approved_date)) {

            // Kumpulin SEMUA nomor WA dari segala penjuru kasta
            $pics = User::getPIC($deptId);
            $leaders = User::getLeader($deptId);
            $supervisors = User::getSupervisor($deptId);
            $managements = User::getManagement();

            // Gabungin semua jadi satu array dan pastiin gak ada nomor ganda
            $allTargets = collect($pics)->merge($leaders)->merge($supervisors)->merge($managements)
                ->unique('id');

            // Kalau ada nomornya, ledakin notifnya! 💥
            if (! empty($allTargets)) {
                $msg = "New Project For {$customerName} Project {$project->model}\n".
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n".
                    "SUDAH SELESAI.\n".
                    "MOHON DILAKUKAN MONITORING SELAMA 3 BULAN PERTAMA.\n\n".
                    'Terima kasih atas supportnya.';

                BroadcastService::send($allTargets, $msg, "Notification Project $project->model");
            }
        }
    }

    /**
     * Handle the ApprovalStatus "deleted" event.
     */
    public function deleted(ApprovalStatus $approvalStatus): void
    {
        //
    }

    /**
     * Handle the ApprovalStatus "restored" event.
     */
    public function restored(ApprovalStatus $approvalStatus): void
    {
        //
    }

    /**
     * Handle the ApprovalStatus "force deleted" event.
     */
    public function forceDeleted(ApprovalStatus $approvalStatus): void
    {
        //
    }
}
