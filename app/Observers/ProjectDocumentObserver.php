<?php

namespace App\Observers;

use App\Models\ProjectDocument;
use App\Models\User;
use App\Services\BroadcastService;
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
        // 2. KONDISI: PIC INPUT DUE DATE (Semua doc udah ada due_date)
        // =========================================================

        // Cek apakah due_date baru aja di-update dan isinya nggak kosong
        if ($document->isDirty('due_date') && ! empty($document->due_date)) {

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
                if (! $project->customer || ! $project->customer->department_id) {
                    return;
                }

                $deptId = $project->customer->department_id;
                $customerName = $project->customer->name;

                // Cari Leader dari department yang sama (Pastiin ini ngereturn Collection ya)
                $leaders = User::getLeader($deptId);
                $supervisors = User::getSupervisor($deptId);
                $managements = User::getManagement();

                // Kalau targetnya dapet, langsung eksekusi!
                if ($leaders->isNotEmpty()) { // Diganti ke isNotEmpty() biar lebih elegan a la Laravel

                    // 1. PESAN WA (Format Original)
                    $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
                        "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                        "Target Mass Production : {$project->masspro_target}\n" .
                        "Schedule sudah di-input.\n\n" .
                        "Mohon segera di-*check* schedule yang telah dibuat.\n" .
                        'Terima kasih.';

                    // 2. PESAN EMAIL (Format Baru Request Klien)
                    $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
                        "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                        "Target Mass Production : {$project->masspro_target}\n" .
                        "Schedule sudah di-input.\n\n" .
                        "Mohon segera di-*check* schedule yang telah dibuat di *aplikasi new project CAR*.\n" .
                        "Terima kasih.";

                    // 3. MAPPING TO & CC
                    $targetTo = $leaders;

                    // Kosongin dulu CC-nya karena di tahap ini lo gak nyebutin CC,
                    // tapi kalau klien minta ada CC, lo tinggal isi query-nya di sini
                    $targetCc = collect($supervisors)->merge($managements)->unique('id');

                    // 4. EKSEKUSI BATCH JUTSU! 💥
                    BroadcastService::sendBatch(
                        $targetTo,
                        $targetCc,
                        $msgWa,
                        $msgEmail,
                        "Notification Project $project->model" // Subject Email
                    );
                }
            }
        }

        // =========================================================
        // 6. KONDISI: PIC UPLOAD DOCUMENT (ON-GOING PROJECT)
        // =========================================================
        // Trigger: actual_date (atau file_name) baru aja keisi
        if ($document->isDirty('actual_date') && ! empty($document->actual_date)) {

            // Load relasi project, customer, dan documentType
            $document->loadMissing(['project.customer', 'documentType']);
            $project = $document->project;

            // Validasi data
            if (! $project || ! $project->customer || ! $project->customer->department_id) {
                return;
            }

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            // Ambil nama dokumen, kalau ga ada relasinya fallback ke code-nya
            $docName = $document->documentType ? $document->documentType->name : $document->document_type_code;

            // Hitung Target Besok (actual_date + 1 hari)
            // (Pakai translatedFormat biar lebih aman kalau mau format lokal)
            $targetDate = Carbon::parse($document->actual_date)->addDay()->translatedFormat('d F Y');

            // Cari Target (Leader, Supervisor, Management)
            $leaders = User::getLeader($deptId);
            $supervisors = User::getSupervisor($deptId);
            $managements = User::getManagement();

            // Eksekusi kalau minimal ada 1 target
            if ($leaders->isNotEmpty() || $supervisors->isNotEmpty() || $managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Status          : Waiting for Leader to Check\n\n" .
                    "Mohon segera diperiksa.\n" .
                    "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Status          : Waiting for Leader to Check\n\n" .
                    "Mohon segera diperiksa di *aplikasi new project CAR*.\n" .
                    "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $leaders;
                // Jurus andalan: Gabungin Supervisor dan Management ke dalem CC
                $targetCc = collect($supervisors)->merge($managements)->unique('id');

                // 4. TEMBAK BATCH MASSAL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Notification Project {$project->model} - {$docName}" // Subject Email
                );
            }
        }

        // =========================================================
        // 7. KONDISI: LEADER CHECK DOCUMENT (ON-GOING PROJECT)
        // =========================================================
        // Trigger: checked_date baru aja keisi
        if ($document->isDirty('checked_date') && ! empty($document->checked_date)) {

            // Pastikan relasi keload biar gak null pointer exception
            $document->loadMissing(['project.customer', 'documentType']);
            $project = $document->project;

            // Validasi data aman
            if (! $project || ! $project->customer || ! $project->customer->department_id) {
                return;
            }

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            // Ambil nama dokumen
            $docName = $document->documentType ? $document->documentType->name : $document->document_type_code;

            // Hitung Target Besok (checked_date + 1 hari)
            // (Pakai translatedFormat biar lebih elegan)
            $targetDate = Carbon::parse($document->checked_date)->addDay()->translatedFormat('d F Y');

            // Cari Target (Supervisor dan Management)
            $supervisors = User::getSupervisor($deptId);
            $managements = User::getManagement();

            // Eksekusi kalau minimal ada targetnya! 🚀
            if ($supervisors->isNotEmpty() || $managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Status          : Waiting for Supervisor to Approve\n\n" .
                    "Mohon segera disetujui.\n" .
                    "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Status          : Waiting for Supervisor to Approve\n\n" .
                    "Mohon segera disetujui di *aplikasi new project CAR*.\n" .
                    "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $supervisors;
                $targetCc = $managements; // Management masuk jalur tembusan

                // 4. TEMBAK BATCH MASSAL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Notification Project {$project->model} - {$docName}" // Subject Email
                );
            }
        }

        // =========================================================
        // 8. KONDISI: DOKUMEN DI-APPROVE (CEK APAKAH SEMUA SUDAH APPROVED?)
        // =========================================================
        // Trigger: approved_date baru aja keisi
        if ($document->isDirty('approved_date') && ! empty($document->approved_date)) {

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
                if (! $project || ! $project->customer || ! $project->customer->department_id) {
                    return;
                }

                $deptId = $project->customer->department_id;
                $customerName = $project->customer->name;

                // Hitung Target Besok (tanggal approve + 1 hari) pakai format elegan
                $targetDate = Carbon::parse($document->approved_date)->addDay()->translatedFormat('d F Y');

                // Cari Target (Leader, Supervisor, Management)
                $leaders = User::getLeader($deptId);
                $supervisors = User::getSupervisor($deptId);
                $managements = User::getManagement();

                // Blast BATCH MASSAL kalau ada targetnya! 🚀
                if ($leaders->isNotEmpty() || $supervisors->isNotEmpty() || $managements->isNotEmpty()) {

                    // 1. PESAN WA (Format Original)
                    $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
                        "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                        "Semua document sudah diupload sesuai schedule.\n\n" .
                        "Mohon di-check agar bisa segera masspro.\n" .
                        "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n" .
                        "Terima kasih.";

                    // 2. PESAN EMAIL (Format Baru Request Klien)
                    $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
                        "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                        "Semua document sudah diupload sesuai schedule.\n\n" .
                        "Mohon di-check di *aplikasi new project CAR*.\n" .
                        "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n" .
                        "Terima kasih.";

                    // 3. MAPPING TO & CC
                    $targetTo = $leaders;
                    // Gabungin Supervisor dan Management ke kursi VIP (CC)
                    $targetCc = collect($supervisors)->merge($managements)->unique('id');

                    // 4. TEMBAK BATCH MASSAL! 💥
                    BroadcastService::sendBatch(
                        $targetTo,
                        $targetCc,
                        $msgWa,
                        $msgEmail,
                        "Notification Project {$project->model} - All Documents Approved" // Subject Email
                    );
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
