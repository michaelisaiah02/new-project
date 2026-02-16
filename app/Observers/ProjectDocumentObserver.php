<?php

namespace App\Observers;

use App\Models\ProjectDocument;
use App\Models\User;
use App\Services\FonnteService;
use Carbon\Carbon;

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
        // =========================================================
        // 1. KONDISI: PIC INPUT DUE DATE (Semua doc udah ada due_date)
        // =========================================================

        // Cek apakah due_date baru aja di-update dan isinya nggak kosong
        if ($document->isDirty('due_date') && !empty($document->due_date)) {

            $projectId = $document->project_id;

            // Hitung sisa dokumen di project ini yang due_date-nya MASIH KOSONG
            $pendingDocs = ProjectDocument::where('project_id', $projectId)
                ->whereNull('due_date')
                ->count();

            // Kalau sisanya 0, berarti ini dokumen TERAKHIR yang di-input jadwalnya
            if ($pendingDocs == 0) {
                // Load relasi project dan customer biar gampang manggil datanya
                $project = $document->project;
                $project->load('customer');

                // Validasi data biar ga error (jaga-jaga bos)
                if (!$project->customer || !$project->customer->department_id) return;

                $deptId = $project->customer->department_id;
                $customerName = $project->customer->name;

                // Cari Leader dari department yang sama
                $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();

                // Kalau nomornya ada, eksekusi WA-nya!
                if (!empty($leaders)) {
                    $msg = "New Project For {$customerName} Project {$project->model}\n" .
                        "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                        "Target Mass Production : {$project->masspro_target}\n" .
                        "Schedule sudah di-input.\n\n" .
                        "Mohon segera di-*check* schedule yang telah dibuat.\n" .
                        "Terima kasih.";

                    FonnteService::send(implode(',', $leaders), $msg);
                }
            }
        }

        // =========================================================
        // 2. KONDISI: PIC UPLOAD DOCUMENT (ON-GOING PROJECT)
        // =========================================================
        // Trigger: actual_date (atau file_name) baru aja keisi
        if ($document->isDirty('actual_date') && !empty($document->actual_date)) {

            // Load relasi project, customer, dan documentType
            $document->loadMissing(['project.customer', 'documentType']);
            $project = $document->project;

            // Validasi data
            if (!$project || !$project->customer || !$project->customer->department_id) return;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            // Ambil nama dokumen, kalau ga ada relasinya fallback ke code-nya
            $docName = $document->documentType ? $document->documentType->name : $document->document_type_code;

            // Hitung Target Besok (actual_date + 1 hari)
            // Formatnya jadi misal: 17 February 2026
            $targetDate = Carbon::parse($document->actual_date)->addDay()->format('d F Y');

            // Cari Leader
            $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();

            // Eksekusi WA ke Leader
            if (!empty($leaders)) {
                $msg = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Status          : Waiting for Leader to Check\n\n" .
                    "Mohon segera diperiksa.\n" .
                    "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $leaders), $msg);
            }
        }

        // =========================================================
        // 3. KONDISI: LEADER CHECK DOCUMENT (ON-GOING PROJECT)
        // =========================================================
        // Trigger: checked_date baru aja keisi
        if ($document->isDirty('checked_date') && !empty($document->checked_date)) {

            // Pastikan relasi keload biar gak null pointer exception
            $document->loadMissing(['project.customer', 'documentType']);
            $project = $document->project;

            // Validasi data aman
            if (!$project || !$project->customer || !$project->customer->department_id) return;

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            // Ambil nama dokumen
            $docName = $document->documentType ? $document->documentType->name : $document->document_type_code;

            // Hitung Target Besok (checked_date + 1 hari)
            $targetDate = Carbon::parse($document->checked_date)->addDay()->format('d F Y');

            // Cari Supervisor dari dept yang sama
            $supervisors = User::getSupervisor($deptId)->pluck('whatsapp')->toArray();

            // Eksekusi WA ke Supervisor! 🚀
            if (!empty($supervisors)) {
                $msg = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Status          : Waiting for Supervisor to Approve\n\n" .
                    "Mohon segera disetujui.\n" .
                    "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n" .
                    "Terima kasih.";

                FonnteService::send(implode(',', $supervisors), $msg);
            }
        }

        // =========================================================
        // 4. KONDISI: DOKUMEN DI-APPROVE (CEK APAKAH SEMUA SUDAH APPROVED?)
        // =========================================================
        // Trigger: approved_date baru aja keisi
        if ($document->isDirty('approved_date') && !empty($document->approved_date)) {

            $projectId = $document->project_id;

            // Cek apakah masih ada dokumen lain di project ini yang BELUM di-approve?
            $pendingDocs = ProjectDocument::where('project_id', $projectId)
                ->whereNull('approved_date')
                ->count();

            // Jika hitungannya 0, berarti ini adalah dokumen TERAKHIR yang di-approve! (BINGO)
            if ($pendingDocs == 0) {

                // Pastikan relasi ke-load biar datanya komplit
                $document->loadMissing(['project.customer']);
                $project = $document->project;

                // Validasi anti-error
                if (!$project || !$project->customer || !$project->customer->department_id) return;

                $deptId = $project->customer->department_id;
                $customerName = $project->customer->name;

                // Hitung Target Besok (tanggal approve + 1 hari)
                $targetDate = Carbon::parse($document->approved_date)->addDay()->format('d F Y');

                // Cari Leader dari department yang sama
                $leaders = User::getLeader($deptId)->pluck('whatsapp')->toArray();

                // Blast WA ke Leader! 🚀
                if (!empty($leaders)) {
                    $msg = "New Project For {$customerName} Project {$project->model}\n" .
                        "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                        "Semua document sudah diupload sesuai schedule.\n\n" .
                        "Mohon di-check agar bisa segera masspro.\n" .
                        "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n" .
                        "Terima kasih.";

                    FonnteService::send(implode(',', $leaders), $msg);
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
