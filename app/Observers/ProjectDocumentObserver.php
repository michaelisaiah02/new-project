<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ProjectDocument;
use App\Services\FonnteService;

class ProjectDocumentObserver
{
    /**
     * Handle the ProjectDocument "created" event.
     */
    public function created(ProjectDocument $projectDocument): void
    {
        //
    }

    /**
     * Handle the ProjectDocument "updated" event.
     */
    public function updated(ProjectDocument $document)
    {
        // Cek jika yang diupdate adalah due_date (dan tidak kosong)
        if ($document->isDirty('due_date') && !empty($document->due_date)) {

            $projectId = $document->project_id;

            // Cek apakah masih ada dokumen lain di project ini yang due_date-nya NULL?
            $pendingDocs = ProjectDocument::where('project_id', $projectId)
                ->whereNull('due_date')
                ->count();

            // Jika pendingDocs == 0, berarti INI ADALAH DOKUMEN TERAKHIR yang diisi
            if ($pendingDocs == 0) {
                // Ambil data project & customer buat pesan
                $project = $document->project;
                $project->load('customer');

                if (!$project->customer || !$project->customer->department_id) return;

                $deptId = $project->customer->department_id;
                $customerName = $project->customer->name;

                // Target: Leader di Department yang sama
                $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();

                if (!empty($leaders)) {
                    $msg = "[New Project For {$customerName}]\n\n" .
                        "Project       : {$project->model}\n" .
                        "No Part       : {$project->part_number}\n" .
                        "Nama Part     : {$project->part_name}\n" .
                        "Activity      : Document Selection + Input Due Date\n" .
                        "Status        : Waiting for Leader to Check\n\n" .
                        "Mohon lakukan pengecekan document dan due date segera.\n" .
                        "Terima kasih.";

                    FonnteService::send(implode(',', $leaders), $msg);
                }
            }
        }

        // Pre-load data yang dibutuhkan (Project, Customer, DocumentType)
        // Kita load sekalian biar ga query berkali-kali
        $document->load(['project.customer', 'documentType']);

        $project = $document->project;

        // Validasi data dasar
        if (!$project || !$project->customer || !$project->customer->department_id) return;

        $deptId = $project->customer->department_id;
        $customerName = $project->customer->name;
        $docName = $document->documentType->name ?? $document->document_type_code; // Fallback ke code kalo name ga ada


        // --- KONDISI 1: DOKUMEN DIUPLOAD (Created Date Terisi) ---
        // Target: Leader
        if ($document->isDirty('created_date') && !empty($document->created_date)) {

            $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();

            if (!empty($leaders)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Status        : Waiting for Leader to Check\n\n" .
                    "Mohon lakukan pengecekan document segera.";

                FonnteService::send(implode(',', $leaders), $msg);
            }
        }

        // --- KONDISI 2: DOKUMEN DICEK (Checked Date Terisi) ---
        // Target: Supervisor
        if ($document->isDirty('checked_date') && !empty($document->checked_date)) {

            $supervisors = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();

            if (!empty($supervisors)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" . // Activity tetap Upload Document atau mau ganti Checking? (Sesuai request lo: Upload Document)
                    "Doc Name      : {$docName}\n" .
                    "Status        : Waiting for Supervisor to Approve\n\n" .
                    "Mohon lakukan approval document segera.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $supervisors), $msg);
            }
        }

        // --- KONDISI: SEMUA DOKUMEN SUDAH DI-APPROVE (Trigger Logic Ongoing) ---
        // Trigger: approved_date terisi
        if ($document->isDirty('approved_date') && !empty($document->approved_date)) {

            // Cek apakah masih ada dokumen lain di project ini yang BELUM di-approve?
            $pendingDocs = ProjectDocument::where('project_id', $project->id)
                ->whereNull('approved_date')
                ->count();

            // Jika pendingDocs == 0, berarti ini dokumen TERAKHIR yang di-approve
            if ($pendingDocs == 0) {

                // A. Kirim Notif ke LEADER
                $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();
                if (!empty($leaders)) {
                    $msgLeader = "[New Project For {$customerName}]\n\n" .
                        "Project       : {$project->model}\n" .
                        "No Part       : {$project->part_number}\n" .
                        "Nama Part     : {$project->part_name}\n" .
                        "Activity      : Approve All Doc Requirement\n" .
                        "Status        : Waiting for Leader to Check\n\n" .
                        "Mohon lakukan pengecekan semua document new project\n" .
                        "Terima kasih.";

                    FonnteService::send(implode(',', $leaders), $msgLeader);
                }

                // B. Kirim Notif ke SUPERVISOR & MANAGEMENT
                $spvs = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();
                $mgmts = User::getManagement()->pluck('whatsapp')->toArray();

                $targetsB = array_unique(array_merge($spvs, $mgmts));

                if (!empty($targetsB)) {
                    $msgSpvMgmt = "[New Project For {$customerName}]\n\n" .
                        "Project       : {$project->model}\n" .
                        "No Part       : {$project->part_number}\n" .
                        "Nama Part     : {$project->part_name}\n" .
                        "Activity      : Approve All Doc Requirement\n" .
                        "Status        : Waiting for Leader to Check\n\n" .
                        "Mohon perintahkan Leader untuk lakukan pengecekan semua document new project.\n" .
                        "Terima kasih.";

                    FonnteService::send(implode(',', $targetsB), $msgSpvMgmt);
                }
            }
        }
    }

    /**
     * Handle the ProjectDocument "deleted" event.
     */
    public function deleted(ProjectDocument $projectDocument): void
    {
        //
    }

    /**
     * Handle the ProjectDocument "restored" event.
     */
    public function restored(ProjectDocument $projectDocument): void
    {
        //
    }

    /**
     * Handle the ProjectDocument "force deleted" event.
     */
    public function forceDeleted(ProjectDocument $projectDocument): void
    {
        //
    }
}
