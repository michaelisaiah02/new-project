<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Services\FonnteService;
use Illuminate\Console\Command;

class ProjectReminderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:project-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi reminder project otomatis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Jalanin Project Reminder...');

        // Hitung tanggal H+7 yang lalu (berarti project dibuat tanggal segini)
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();

        // === 1. REMINDER INPUT NEW PROJECT (H+7) ===
        // Cari project yg dibuat 7 hari lalu DAN (Approval Status Created Date NULL ATAU Ada Dokumen yg Due Date NULL)
        $projectsH7 = Project::whereDate('created_at', $sevenDaysAgo)
            ->where(function ($query) {
                $query->whereHas('approvalStatus', function ($q) {
                    $q->whereNull('created_date');
                })
                    ->orWhereHas('documents', function ($q) {
                        $q->whereNull('due_date');
                    });
            })
            ->with(['customer']) // Eager load customer biar hemat query
            ->get();

        foreach ($projectsH7 as $project) {
            // Validasi data customer & department
            if (!$project->customer || !$project->customer->department_id) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            // Target: PIC Engineering
            $pics = User::getPIC($deptId)->pluck('whatsapp')->toArray();

            if (!empty($pics)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "REMINDER!\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Suffix        : {$project->suffix}\n\n" .
                    "Mohon isikan document yang dibutuhkan dan due date sesuai dengan schedule. Terima kasih.\n" .
                    "Terima kasih.";

                // Kirim Notif
                FonnteService::send(implode(',', $pics), $msg);
            }
        }

        $this->info('Reminder H+7 selesai dieksekusi.');

        $yesterday = Carbon::yesterday()->toDateString();

        // === 2. H+1 PROJECT BELUM CHECKED (Target: LEADER) ===
        // Logic: created_date = kemarin, tapi checked_date masih NULL
        $lateCheckStatus = \App\Models\ApprovalStatus::whereNull('checked_date')
            ->whereDate('created_date', $yesterday)
            ->with('project.customer')
            ->get();

        foreach ($lateCheckStatus as $status) {
            $project = $status->project;
            if (!$project || !$project->customer || !$project->customer->department_id) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();

            if (!empty($leaders)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "REMINDER!\n" .
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


        // === 3. H+1 PROJECT BELUM APPROVED (Target: SUPERVISOR) ===
        // Logic: checked_date = kemarin, tapi approved_date masih NULL
        $lateApproveStatus = \App\Models\ApprovalStatus::whereNull('approved_date')
            ->whereDate('checked_date', $yesterday)
            ->with('project.customer')
            ->get();

        foreach ($lateApproveStatus as $status) {
            $project = $status->project;
            if (!$project || !$project->customer || !$project->customer->department_id) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            $supervisors = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();

            if (!empty($supervisors)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "REMINDER!\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Document Selection + Input Due Date\n" .
                    "Status        : Waiting for Supervisor to Approve\n\n" .
                    "Mohon lakukan approval document dan due date segera.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $supervisors), $msg);
            }
        }


        // === 4. H+1 PROJECT BELUM MGMT APPROVED (Target: MANAGEMENT) ===
        // Logic: approved_date = kemarin, tapi management_approved_date masih NULL
        $lateMgmtApproveStatus = \App\Models\ApprovalStatus::whereNull('management_approved_date')
            ->whereDate('approved_date', $yesterday)
            ->with('project.customer')
            ->get();

        foreach ($lateMgmtApproveStatus as $status) {
            $project = $status->project;
            if (!$project || !$project->customer) continue; // Management gak butuh dept_id sebenernya

            $customerName = $project->customer->name;

            $managements = User::getManagement()->pluck('whatsapp')->toArray();

            if (!empty($managements)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "REMINDER!\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Document Selection + Input Due Date\n" .
                    "Status        : Waiting for Management to Approve\n\n" .
                    "Mohon lakukan approval document dan due date segera.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $managements), $msg);
            }
        }

        // SETUP VARIABLE TANGGAL
        $tomorrow = Carbon::tomorrow()->toDateString();
        $today = Carbon::today()->toDateString();
        $twoDaysAgo = Carbon::now()->subDays(2)->toDateString();

        // ==========================================
        // 5. REMINDER UPLOAD DOKUMEN (H-1)
        // Kondisi: Belum upload (actual_date null) DAN Due Date = BESOK
        // ==========================================
        $docsHMinus1 = ProjectDocument::whereNull('actual_date')
            ->whereDate('due_date', $tomorrow)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($docsHMinus1 as $doc) {
            $project = $doc->project;
            if (!$project || !$project->customer || !$project->customer->department_id) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;
            $docName = $doc->documentType->name ?? $doc->document_type_code;
            $dueDateStr = Carbon::parse($doc->due_date)->format('d F Y');

            // Target: PIC & Leader
            $pics = User::getPIC($deptId)->pluck('whatsapp')->toArray();
            $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();
            $targets = array_unique(array_merge($pics, $leaders));

            if (!empty($targets)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Due Date      : {$dueDateStr} (H-1)\n\n" .
                    "Mohon disiapkan dan diupload segera.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $targets), $msg);
            }
        }

        // ==========================================
        // 6. REMINDER UPLOAD DOKUMEN (HARI H / TODAY)
        // Kondisi: Belum upload DAN Due Date = HARI INI
        // ==========================================
        $docsHToday = ProjectDocument::whereNull('actual_date')
            ->whereDate('due_date', $today)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($docsHToday as $doc) {
            $project = $doc->project;
            if (!$project || !$project->customer) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;
            $docName = $doc->documentType->name ?? $doc->document_type_code;
            $dueDateStr = Carbon::parse($doc->due_date)->format('d F Y');

            // Target: PIC & Leader
            $pics = User::getPIC($deptId)->pluck('whatsapp')->toArray();
            $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();
            $targets = array_unique(array_merge($pics, $leaders));

            if (!empty($targets)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Due Date      : {$dueDateStr} (TODAY)\n\n" .
                    "Mohon disiapkan dan diupload segera.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $targets), $msg);
            }
        }

        // ==========================================
        // 7. REMINDER UPLOAD OVERDUE (H+2)
        // Kondisi: Belum upload DAN Due Date = 2 HARI LALU
        // ==========================================
        $docsOverdue = ProjectDocument::whereNull('actual_date')
            ->whereDate('due_date', $twoDaysAgo)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($docsOverdue as $doc) {
            $project = $doc->project;
            if (!$project || !$project->customer) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;
            $docName = $doc->documentType->name ?? $doc->document_type_code;
            $dueDateStr = Carbon::parse($doc->due_date)->format('d F Y');

            // Target: Leader, Supervisor, Management (ESKALASI)
            $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();
            $spvs = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();
            $mgmts = User::getManagement()->pluck('whatsapp')->toArray();

            $targets = array_unique(array_merge($leaders, $spvs, $mgmts));

            if (!empty($targets)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Due Date      : {$dueDateStr} (Overdue H+2)\n\n" .
                    "Mohon perintahkan PIC untuk upload!\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $targets), $msg);
            }
        }

        // ==========================================
        // 8. SUDAH UPLOAD TAPI BELUM CHECKED (H+2 DARI UPLOAD)
        // Kondisi: Actual Date isi, Checked Date NULL, Upload Date = 2 HARI LALU
        // ==========================================
        $docsUnchecked = ProjectDocument::whereNotNull('actual_date')
            ->whereNull('checked_date')
            // Kita pakai created_date sebagai acuan kapan dia upload
            ->whereDate('created_date', $twoDaysAgo)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($docsUnchecked as $doc) {
            $project = $doc->project;
            if (!$project || !$project->customer) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;
            $docName = $doc->documentType->name ?? $doc->document_type_code;

            // Target: Leader
            $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();

            if (!empty($leaders)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "REMINDER!\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Status        : Waiting for Leader to Check\n\n" .
                    "Mohon lakukan pengecekan document segera.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $leaders), $msg);
            }
        }

        // ==========================================
        // 9. REMINDER CHECK DOKUMEN (H-1)
        // Kondisi: Sudah Upload (actual != null), Belum Check (checked == null), Due Date = BESOK
        // ==========================================
        $docsCheckHMin1 = ProjectDocument::whereNotNull('actual_date')
            ->whereNull('checked_date')
            ->whereDate('due_date', $tomorrow)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($docsCheckHMin1 as $doc) {
            $project = $doc->project;
            if (!$project || !$project->customer) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;
            $docName = $doc->documentType->name ?? $doc->document_type_code;
            $dueDateStr = Carbon::parse($doc->due_date)->format('d F Y');

            // A. Kirim ke LEADER
            $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();
            if (!empty($leaders)) {
                $msgLeader = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Due Date      : {$dueDateStr} (H-1)\n" .
                    "Status        : Waiting for Leader to Check.\n\n" .
                    "Mohon lakukan pengecekan document segera.\n" .
                    "Terima kasih.";
                FonnteService::send(implode(',', $leaders), $msgLeader);
            }

            // B. Kirim ke SUPERVISOR
            $spvs = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();
            if (!empty($spvs)) {
                $msgSpv = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Due Date      : {$dueDateStr} (H-1)\n" .
                    "Status        : Waiting for Leader to Check.\n\n" .
                    "Mohon perintahkan Leader untuk cek document segera.\n" .
                    "Terima kasih.";
                FonnteService::send(implode(',', $spvs), $msgSpv);
            }
        }

        // ==========================================
        // 10. REMINDER CHECK DOKUMEN (HARI H / TODAY)
        // Kondisi: Sudah Upload, Belum Check, Due Date = HARI INI
        // ==========================================
        $docsCheckToday = ProjectDocument::whereNotNull('actual_date')
            ->whereNull('checked_date')
            ->whereDate('due_date', $today)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($docsCheckToday as $doc) {
            $project = $doc->project;
            if (!$project || !$project->customer) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;
            $docName = $doc->documentType->name ?? $doc->document_type_code;
            $dueDateStr = Carbon::parse($doc->due_date)->format('d F Y');

            // A. Kirim ke LEADER
            $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();
            if (!empty($leaders)) {
                $msgLeader = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Due Date      : {$dueDateStr} (TODAY)\n" .
                    "Status        : Waiting for Leader to Check.\n\n" .
                    "Mohon lakukan pengecekan document segera.\n" .
                    "Terima kasih.";
                FonnteService::send(implode(',', $leaders), $msgLeader);
            }

            // B. Kirim ke SUPERVISOR
            $spvs = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();
            if (!empty($spvs)) {
                $msgSpv = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Due Date      : {$dueDateStr} (TODAY)\n" .
                    "Status        : Waiting for Leader to Check.\n\n" .
                    "Mohon perintahkan Leader untuk cek document segera.\n" .
                    "Terima kasih.";
                FonnteService::send(implode(',', $spvs), $msgSpv);
            }
        }

        // ==========================================
        // 11. REMINDER CHECK OVERDUE (H+2)
        // Kondisi: Sudah Upload, Belum Check, Due Date = 2 Hari Lalu
        // Target: Supervisor & Management
        // ==========================================
        $docsCheckOverdue = ProjectDocument::whereNotNull('actual_date')
            ->whereNull('checked_date')
            ->whereDate('due_date', $twoDaysAgo)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($docsCheckOverdue as $doc) {
            $project = $doc->project;
            if (!$project || !$project->customer) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;
            $docName = $doc->documentType->name ?? $doc->document_type_code;
            $dueDateStr = Carbon::parse($doc->due_date)->format('d F Y');

            // Gabung Target SPV & Management
            $spvs = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();
            $mgmts = User::getManagement()->pluck('whatsapp')->toArray();
            $targets = array_unique(array_merge($spvs, $mgmts));

            if (!empty($targets)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Due Date      : {$dueDateStr} (Overdue H+2)\n" .
                    "Status        : Waiting for Leader to Check\n\n" .
                    "Mohon perintahkan Leader untuk cek segera!\n" .
                    "Terima kasih.";
                FonnteService::send(implode(',', $targets), $msg);
            }
        }

        // ==========================================
        // 12. REMINDER APPROVE (H+2 SETELAH CHECKED)
        // Kondisi: Sudah Checked, Belum Approved, Checked Date = 2 Hari Lalu
        // Target: Supervisor
        // ==========================================
        $docsApproveLate = ProjectDocument::whereNotNull('checked_date')
            ->whereNull('approved_date')
            ->whereDate('checked_date', $twoDaysAgo) // SLA 2 hari dari tanggal check
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($docsApproveLate as $doc) {
            $project = $doc->project;
            if (!$project || !$project->customer) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;
            $docName = $doc->documentType->name ?? $doc->document_type_code;

            $spvs = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();

            if (!empty($spvs)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "REMINDER!\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Status        : Waiting for Supervisor to Approve\n\n" .
                    "Mohon lakukan approval document segera.\n" .
                    "Terima kasih.";
                FonnteService::send(implode(',', $spvs), $msg);
            }
        }

        // ==========================================
        // 13. REMINDER APPROVE DOKUMEN (H-1)
        // Kondisi: Sudah Checked, Belum Approved, Due Date = BESOK
        // Target: Supervisor
        // ==========================================
        $docsApproveHMin1 = ProjectDocument::whereNotNull('checked_date')
            ->whereNull('approved_date')
            ->whereDate('due_date', $tomorrow)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($docsApproveHMin1 as $doc) {
            $project = $doc->project;
            if (!$project || !$project->customer) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;
            $docName = $doc->documentType->name ?? $doc->document_type_code;
            $dueDateStr = Carbon::parse($doc->due_date)->format('d F Y');

            $spvs = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();

            if (!empty($spvs)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Due Date      : {$dueDateStr} (H-1)\n" .
                    "Status        : Waiting for Supervisor to Approve.\n\n" .
                    "Mohon lakukan approval document segera.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $spvs), $msg);
            }
        }

        // ==========================================
        // 14. REMINDER APPROVE DOKUMEN (OVERDUE H+1)
        // Kondisi: Sudah Checked, Belum Approved, Due Date = KEMARIN
        // Target: Management
        // ==========================================
        $docsApproveHPlus1 = ProjectDocument::whereNotNull('checked_date')
            ->whereNull('approved_date')
            ->whereDate('due_date', $yesterday)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($docsApproveHPlus1 as $doc) {
            $project = $doc->project;
            if (!$project || !$project->customer) continue;

            // Management biasanya gak butuh deptId, tapi kita ambil nama customer
            $customerName = $project->customer->name;
            $docName = $doc->documentType->name ?? $doc->document_type_code;
            $dueDateStr = Carbon::parse($doc->due_date)->format('d F Y');

            $managements = User::getManagement()->pluck('whatsapp')->toArray();

            if (!empty($managements)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Upload Document\n" .
                    "Doc Name      : {$docName}\n" .
                    "Due Date      : {$dueDateStr} (Overdue H+1)\n" .
                    "Status        : Waiting for Supervisor to Approve.\n\n" .
                    "Mohon lakukan approval document segera.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $managements), $msg);
            }
        }

        // SETUP VARIABLE TANGGAL H+3
        $threeDaysAgo = Carbon::now()->subDays(3)->toDateString();

        // ==========================================
        // 15. REMINDER ONGOING CHECK (H+3 DARI LAST DOC APPROVED)
        // Kondisi: Semua Dokumen Approved, Tapi Leader Belum Klik Ongoing Check
        // Trigger: Last Doc Approved Date = 3 Hari Lalu
        // ==========================================

        // Cari status yg belum di-check ongoing-nya
        $ongoingCheckPending = \App\Models\ApprovalStatus::whereNull('ongoing_checked_date')
            ->with('project.documents') // Eager load docs
            ->get();

        foreach ($ongoingCheckPending as $status) {
            $project = $status->project;
            if (!$project || $project->documents->isEmpty()) continue;

            // Cek 1: Apakah ada dokumen yg BELUM approved?
            $unapprovedDocsCount = $project->documents->whereNull('approved_date')->count();
            if ($unapprovedDocsCount > 0) continue; // Skip kalau belum beres semua

            // Cek 2: Ambil tanggal approve paling terakhir (Max Approved Date)
            $lastApprovedDate = $project->documents->max('approved_date'); // Format Y-m-d

            // Cek 3: Apakah tanggal terakhir approve itu adalah 3 HARI LALU?
            if ($lastApprovedDate == $threeDaysAgo) {

                if (!$project->customer) continue;
                $deptId = $project->customer->department_id;
                $customerName = $project->customer->name;

                // Target: LEADER
                $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();

                if (!empty($leaders)) {
                    $msg = "[New Project For {$customerName}]\n\n" .
                        "Project       : {$project->model}\n" .
                        "No Part       : {$project->part_number}\n" .
                        "Nama Part     : {$project->part_name}\n" .
                        "Activity      : Approve All Doc Requirement\n" .
                        "Status        : Waiting for Leader to Check\n\n" .
                        "Mohon lakukan pengecekan semua document new project\n" .
                        "Terima kasih.";

                    FonnteService::send(implode(',', $leaders), $msg);
                }
            }
        }

        // ==========================================
        // 16. REMINDER ONGOING APPROVE (H+3 DARI CHECKED)
        // Kondisi: Ongoing Checked, Tapi Belum Ongoing Approved
        // Trigger: Ongoing Checked Date = 3 Hari Lalu
        // ==========================================
        $ongoingApprovePending = \App\Models\ApprovalStatus::whereNotNull('ongoing_checked_date')
            ->whereNull('ongoing_approved_date')
            ->whereDate('ongoing_checked_date', $threeDaysAgo)
            ->with('project.customer')
            ->get();

        foreach ($ongoingApprovePending as $status) {
            $project = $status->project;
            if (!$project || !$project->customer) continue;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            // Target: SUPERVISOR
            $spvs = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();

            if (!empty($spvs)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "REMINDER!\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Approve All Doc Requirement\n" .
                    "Status        : Waiting for Supervisor to Approve\n\n" .
                    "Mohon lakukan pengecekan semua document new project.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $spvs), $msg);
            }
        }

        // ==========================================
        // 17. REMINDER ONGOING MGMT APPROVE (H+3 DARI APPROVED)
        // Kondisi: Ongoing Approved, Tapi Belum Mgmt Approved
        // Trigger: Ongoing Approved Date = 3 Hari Lalu
        // ==========================================
        $ongoingMgmtPending = \App\Models\ApprovalStatus::whereNotNull('ongoing_approved_date')
            ->whereNull('ongoing_management_approved_date')
            ->whereDate('ongoing_approved_date', $threeDaysAgo)
            ->with('project.customer')
            ->get();

        foreach ($ongoingMgmtPending as $status) {
            $project = $status->project;
            if (!$project || !$project->customer) continue;

            $customerName = $project->customer->name;

            // Target: MANAGEMENT
            $managements = User::getManagement()->pluck('whatsapp')->toArray();

            if (!empty($managements)) {
                $msg = "[New Project For {$customerName}]\n\n" .
                    "Project       : {$project->model}\n" .
                    "No Part       : {$project->part_number}\n" .
                    "Nama Part     : {$project->part_name}\n" .
                    "Activity      : Approve All Doc Requirement\n" .
                    "Status        : Waiting for Management to Approve\n\n" .
                    "Mohon lakukan persetujuan semua document new project.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $managements), $msg);
            }
        }
    }
}
