<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ApprovalStatus;
use App\Services\FonnteService;

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
        // Load Project & Customer biar datanya lengkap
        $status->load(['project.customer']);
        $project = $status->project;

        if (!$project || !$project->customer || !$project->customer->department_id) return;

        $deptId = $project->customer->department_id;
        $customerName = $project->customer->name;

        // --- POIN 2: LEADER CHECKED -> NOTIF SUPERVISOR ---
        // Trigger: checked_date terisi
        if ($status->isDirty('checked_date') && !empty($status->checked_date)) {

            // Target: Supervisor Dept Terkait
            $supervisors = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();

            if (!empty($supervisors)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Document Selection + Input Due Date\n" .
                    "Status        : Waiting for Supervisor to Approve\n\n" . // Note: Sesuai request (walaupun checked, status nextnya waiting spv)
                    "Mohon lakukan approval document dan due date segera.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $supervisors), $msg);
            }
        }

        // --- POIN 3: SUPERVISOR APPROVED -> NOTIF MANAGEMENT ---
        // Trigger: approved_date terisi
        if ($status->isDirty('approved_date') && !empty($status->approved_date)) {

            // Target: Management
            $managements = User::getManagement()->pluck('whatsapp')->toArray();

            if (!empty($managements)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Document Selection + Input Due Date\n" .
                    "Status        : Waiting for Management to Approve\n\n" . // Note: Gue sesuaikan jadi Management ya textnya biar make sense
                    "Mohon lakukan approval document dan due date segera.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $managements), $msg);
            }
        }

        // --- POIN 4: MANAGEMENT APPROVED -> NOTIF ALL USERS ---
        // Trigger: management_approved_date terisi
        if ($status->isDirty('management_approved_date') && !empty($status->management_approved_date)) {

            // Target: SEMUA (PIC, Leader, Spv Dept Terkait + Management)
            $pics = User::getPIC($deptId)->pluck('whatsapp')->toArray();
            $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();
            $spvs = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();
            $mgmts = User::getManagement()->pluck('whatsapp')->toArray();

            $allTargets = array_unique(array_merge($pics, $leaders, $spvs, $mgmts));

            if (!empty($allTargets)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Document Selection + Input Due Date\n" .
                    "Status        : Management Has Approved\n\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $allTargets), $msg);
            }
        }

        // --- POIN 2: ONGOING PROJECT CHECKED (Leader check all) ---
        // Trigger: ongoing_checked_date terisi
        if ($status->isDirty('ongoing_checked_date') && !empty($status->ongoing_checked_date)) {

            // A. Notif ke PIC
            $pics = User::getPIC($deptId)->pluck('whatsapp')->toArray();
            if (!empty($pics)) {
                $msgPIC = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Approve All Doc Requirement\n" .
                    "Status        : Checked and waiting for Supervisor to Approve\n\n" .
                    "Terima kasih.";
                FonnteService::send(implode(',', $pics), $msgPIC);
            }

            // B. Notif ke SUPERVISOR
            $supervisors = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();
            if (!empty($supervisors)) {
                $msgSpv = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Approve All Doc Requirement\n" .
                    "Status        : Waiting for Supervisor to Approve\n\n" .
                    "Mohon lakukan pengecekan semua document new project.\n" .
                    "Terima kasih.";
                FonnteService::send(implode(',', $supervisors), $msgSpv);
            }
        }

        // --- POIN 3: ONGOING PROJECT APPROVED (Spv approve all) ---
        // Trigger: ongoing_approved_date terisi
        if ($status->isDirty('ongoing_approved_date') && !empty($status->ongoing_approved_date)) {

            // A. Notif ke PIC
            $pics = User::getPIC($deptId)->pluck('whatsapp')->toArray();
            if (!empty($pics)) {
                $msgPIC = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Approve All Doc Requirement\n" .
                    "Status        : Approved and waiting for Management to Approve\n\n" .
                    "Terima kasih.";
                FonnteService::send(implode(',', $pics), $msgPIC);
            }

            // B. Notif ke MANAGEMENT
            $managements = User::getManagement()->pluck('whatsapp')->toArray();
            if (!empty($managements)) {
                $msgMgmt = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Approve All Doc Requirement\n" .
                    "Status        : Waiting for Management to Approve\n\n" .
                    "Mohon lakukan persetujuan semua document new project.\n" .
                    "Terima kasih.";
                FonnteService::send(implode(',', $managements), $msgMgmt);
            }
        }

        // --- POIN 4: ONGOING PROJECT MGMT APPROVED (Final) ---
        // Trigger: ongoing_management_approved_date terisi
        if ($status->isDirty('ongoing_management_approved_date') && !empty($status->ongoing_management_approved_date)) {

            // Target: SEMUA USER TERKAIT
            $pics = User::getPIC($deptId)->pluck('whatsapp')->toArray();
            $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();
            $spvs = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();
            $mgmts = User::getManagement()->pluck('whatsapp')->toArray();

            $allTargets = array_unique(array_merge($pics, $leaders, $spvs, $mgmts));

            if (!empty($allTargets)) {
                $msgAll = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Approve All Doc Requirement\n" .
                    "Status        : Approved By Management\n\n" .
                    "Terima kasih atas kerja kerasnya, Project ini akan masuk ke \"List Mass Production\"\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $allTargets), $msgAll);
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
