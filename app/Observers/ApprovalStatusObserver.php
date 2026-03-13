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
        // 3. KONDISI: LEADER SUDAH CHECK SCHEDULE
        // =========================================================

        // Cek apakah kolom checked_date baru aja berubah dan ada isinya
        if ($status->isDirty('checked_date') && ! empty($status->checked_date)) {

            // Cari Supervisor dari department yang terkait (Pastikan return-nya Collection ya)
            $supervisors = User::getSupervisor($deptId);
            $managements = User::getManagement();

            // Ganti pake isNotEmpty() biar standar Laravel-nya dapet!
            if ($supervisors->isNotEmpty()) {

                // 1. PESAN WA (Original)
                $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "Schedule sudah di-checked.\n\n" .
                    "Mohon segera di-*approve* schedule yang telah dibuat.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Request Klien)
                $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "Schedule sudah di-checked.\n\n" .
                    "Mohon segera di-*approve* schedule yang telah dibuat di *aplikasi new project CAR*.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $supervisors;
                $targetCc = $managements;

                // 4. TEMBAK BATCH MASSAL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Supervisor Terkait", // Sapaan Divisi
                    "Notification Project $project->model" // Judul Email
                );
            }
        }

        // =========================================================
        // 4. KONDISI: SUPERVISOR SUDAH APPROVE SCHEDULE
        // =========================================================

        // Cek apakah kolom approved_date baru aja berubah dan ada isinya
        if ($status->isDirty('approved_date') && ! empty($status->approved_date)) {

            // Cari orang-orang Management (Gak perlu pake $deptId karena management biasanya global)
            $managements = User::getManagement();

            // Pake isNotEmpty() ya bos biar elegan!
            if ($managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "Schedule sudah di-approved.\n\n" .
                    "Mohon segera *disetujui* schedule yang telah dibuat, agar bisa dimulai new project ini.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "Schedule sudah di-approved.\n\n" .
                    "Mohon segera *disetujui* schedule yang telah dibuat di *aplikasi new project CAR*, agar bisa dimulai new project ini.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $managements;
                $targetCc = collect(); // Management di-TO semua, CC kosongin aja

                // 4. TEMBAK BATCH MASSAL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Management Terkait", // Sapaan Divisi: "Kepada Yth. Management Terkait,"
                    "Notification Project $project->model" // Judul Email
                );
            }
        }

        // =========================================================
        // 5. KONDISI: MANAGEMENT SUDAH APPROVE (RESMI ON-GOING PROJECT)
        // =========================================================

        // Cek apakah kolom management_approved_date baru aja berubah dan ada isinya
        if ($status->isDirty('management_approved_date') && ! empty($status->management_approved_date)) {

            // Cari Leader dan Supervisor dari department yang terkait
            $leaders = User::getLeader($deptId);
            $supervisors = User::getSupervisor($deptId);
            $pics = User::getPIC($deptId);

            // Gabungin target (buat validasi kalau emang ada orangnya)
            $targets = collect($leaders)->merge($supervisors)->unique('id');

            // Kalau datanya nggak kosong, langsung blast!
            if ($targets->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "*SUDAH DIMULAI (ON-GOING PROJECT)*\n\n" .
                    "Mohon dilakukan monitoring hingga masspro.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                // Di sini isi pesannya kebetulan sama persis sama WA, tapi kita tetep pisah
                // variabel biar gampang kalau besok-besok si klien bawel minta diubah lagi wkwk
                $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "*SUDAH DIMULAI (ON-GOING PROJECT)*\n\n" .
                    "Mohon dilakukan monitoring hingga masspro.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                // Biar rapi a la email korporat: Leader di TO, Supervisor di CC
                $targetTo = $leaders;
                $targetCc = collect($pics)->merge($supervisors)->unique('id');

                // 4. TEMBAK BATCH MASSAL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Leader & Supervisor Terkait", // Sapaan Divisi: "Kepada Yth. Leader & Supervisor Terkait,"
                    "Notification Project $project->model" // Judul Email
                );
            }
        }

        // =========================================================
        // 9. KONDISI: LEADER SUDAH CHECK ON-GOING PROJECT (SEMUA DOC LENGKAP)
        // =========================================================

        // Cek apakah kolom ongoing_checked_date baru aja berubah dan ada isinya
        if ($status->isDirty('ongoing_checked_date') && ! empty($status->ongoing_checked_date)) {

            // Hitung Target Besok (tanggal checked + 1 hari) pakai format elegan
            $targetDate = Carbon::parse($status->ongoing_checked_date)->addDay()->translatedFormat('d F Y');

            // Cari Target (Supervisor dan Management)
            $supervisors = User::getSupervisor($deptId);
            $managements = User::getManagement();

            // Eksekusi kalau targetnya eksis! 🚀
            if ($supervisors->isNotEmpty() || $managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                    "Semua document sudah di-check.\n\n" .
                    "Mohon di-approve agar bisa segera masspro.\n" .
                    "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                    "Semua document sudah di-check.\n\n" .
                    "Mohon di-approve di *aplikasi new project CAR*.\n" .
                    "Target besok, tgl {$targetDate} harus sudah dikerjakan.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $supervisors;
                $targetCc = $managements; // Bos-bos dapet tembusan

                // 4. TEMBAK BATCH MASSAL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Supervisor & Management Terkait", // Sapaan Divisi
                    "Notification Project {$project->model} - All Documents Checked" // Subject Email
                );
            }
        }

        // =========================================================
        // 10. KONDISI: SUPERVISOR SUDAH APPROVE ON-GOING PROJECT
        // =========================================================

        // Cek apakah kolom ongoing_approved_date baru aja berubah dan ada isinya
        if ($status->isDirty('ongoing_approved_date') && ! empty($status->ongoing_approved_date)) {

            // Hitung Target Besok (tanggal approved + 1 hari) pakai translatedFormat
            $targetDate = Carbon::parse($status->ongoing_approved_date)->addDay()->translatedFormat('d F Y');

            // Cari user Management (Global, gak pake $deptId)
            $managements = User::getManagement();

            // Kalau datanya ada, eksekusi! 🚀
            if ($managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                    "Semua document sudah di-approve oleh supervisor.\n\n" .
                    "Mohon di-approve by management agar bisa segera masspro.\n" .
                    "Target besok, tgl {$targetDate} sudah dikerjakan.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                    "Semua document sudah di-approve oleh supervisor.\n\n" .
                    "Mohon di-approve by management di *aplikasi new project CAR*.\n" .
                    "Target besok, tgl {$targetDate} sudah dikerjakan.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $managements;
                $targetCc = collect(); // Sesuai pesanan: CC Kosongin aja!

                // 4. TEMBAK BATCH MASSAL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Management Terkait", // Sapaan Divisi
                    "Notification Project {$project->model} - Supervisor Approved" // Subject Email
                );
            }
        }

        // =========================================================
        // 11. KONDISI: MANAGEMENT SUDAH APPROVE ON-GOING (FINAL PROJECT SELESAI)
        // =========================================================

        // Cek apakah kolom ongoing_management_approved_date baru aja berubah dan ada isinya
        if ($status->isDirty('ongoing_management_approved_date') && ! empty($status->ongoing_management_approved_date)) {

            // Panggil semua kasta dari penjuru departemen!
            $pics = User::getPIC($deptId);
            $leaders = User::getLeader($deptId);
            $supervisors = User::getSupervisor($deptId);
            $managements = User::getManagement();

            // Gabungin semua buat ngecek minimal ada 1 orang ga di project ini
            $allTargets = collect($pics)->merge($leaders)->merge($supervisors)->merge($managements)->unique('id');

            // Kalau ada pasukannya, ledakin notifnya! 💥
            if ($allTargets->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                    "SUDAH SELESAI.\n" .
                    "MOHON DILAKUKAN MONITORING SELAMA 3 BULAN PERTAMA.\n\n" .
                    "Terima kasih atas supportnya.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                    "NEW PROJECT SUDAH SELESAI.\n" .
                    "MOHON DILAKUKAN MONITORING SELAMA 3 BULAN PERTAMA.\n\n" .
                    "Terima kasih atas supportnya.";

                // 3. MAPPING TO & CC
                $targetTo = $leaders; // TO Khusus Leader
                // Sisanya (PIC, SPV, Management) di-merge masuk ke CC
                $targetCc = collect($pics)->merge($supervisors)->merge($managements)->unique('id');

                // 4. TEMBAK BATCH MASSAL FINAL! 💥💥💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Seluruh Tim Terkait", // Sapaan Pukul Rata!
                    "Notification Project {$project->model} - FINAL DONE" // Subject Email
                );
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
