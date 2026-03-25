<?php

namespace App\Console\Commands;

use App\Models\ApprovalStatus;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use App\Services\BroadcastService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProjectReminderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:project-reminders {channel}';

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
        $channel = $this->argument('channel');
        $this->info('Mulai ngecek jadwal...Target Channel: ' . strtoupper($channel));

        // =========================================================
        // 1. REMINDER H+10: PROJECT BARU TAPI DUE DATE BELUM LENGKAP
        // =========================================================
        // Mundur 10 hari ke belakang dari hari ini
        $tenDaysAgo = Carbon::now()->subDays(10)->toDateString();

        // Cari project yang dibikin 10 hari lalu, DAN masih ada dokumen yg due_date-nya nyangkut/kosong
        $projectsH10 = Project::whereDate('created_at', $tenDaysAgo)
            ->whereHas('documents', function ($query) {
                $query->whereNull('due_date');
            })
            ->with(['customer'])
            ->get();

        foreach ($projectsH10 as $project) {
            // Validasi dulu biar script ga nyusruk kalo data relasinya ilang
            if (! $project->customer || ! $project->customer->department_id) {
                continue;
            }

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            // Kumpulin Pasukan (Kasta per departemen)
            $leaders = User::getLeader($deptId);
            $pics = User::getPIC($deptId); // Pastikan lo punya fungsi ini ya
            $supervisors = User::getSupervisor($deptId);
            $managements = User::getManagement();

            // Eksekusi kalau minimal ada Leader-nya
            if ($leaders->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n\n" .
                    "*BELUM DILAKUKAN FOLLOW UP*\n\n" .
                    "Mohon diberitahukan kepada PIC untuk segera membuat schedule document.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n\n" .
                    "*BELUM DILAKUKAN FOLLOW UP*\n\n" .
                    "Mohon diberitahukan kepada PIC untuk segera membuat schedule new project di *aplikasi new project CAR*.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $leaders;
                // CC Selain Leader (Gabungin PIC, Supervisor, Management)
                $targetCc = collect($pics)->merge($supervisors)->merge($managements)->unique('id');

                // 4. TEMBAK BATCH MASSAL DENGAN CHANNEL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Reminder Project {$project->model}", // Subject Email
                    $channel // 👈 Lempar parameter 'wa' atau 'email' dari Cron ke sini!
                );
            }
        }

        // =========================================================
        // 2. REMINDER H+5: SCHEDULE UDAH INPUT TAPI LEADER GHOSTING (BELUM CHECK)
        // =========================================================
        // Mundur 5 hari ke belakang
        $fiveDaysAgo = Carbon::now()->subDays(5)->toDateString();

        // Cari status yang created_date-nya 5 hari lalu, tapi checked_date-nya masih kosong
        $pendingCheckH5 = ApprovalStatus::whereNull('checked_date')
            ->whereDate('created_date', $fiveDaysAgo)
            ->with(['project.customer'])
            ->get();

        foreach ($pendingCheckH5 as $status) {
            $project = $status->project;

            // Validasi data aman sejahtera
            if (! $project || ! $project->customer || ! $project->customer->department_id) {
                continue;
            }

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            // Target korban SP lisan: Supervisor, Tembusan: Management
            $supervisors = User::getSupervisor($deptId);
            $managements = User::getManagement();

            // Kalau nomor target dapet, langsung tembak! 🚀
            if ($supervisors->isNotEmpty() || $managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "Schedule sudah di-input.\n\n" .
                    "Mohon diberitahukan kepada leader untuk segera *check* schedule yang telah dibuat.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "Schedule sudah di-input.\n\n" .
                    "Mohon diberitahukan kepada leader untuk segera *check* schedule yang telah dibuat di *aplikasi new project CAR*.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $supervisors;
                $targetCc = $managements; // Management masuk jalur VIP (CC)

                // 4. TEMBAK BATCH MASSAL DENGAN CHANNEL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Reminder Project {$project->model}", // Subject Email
                    $channel // 👈 Parameter dewa penyelamat dari limit
                );
            }
        }

        // =========================================================
        // 3. REMINDER H+5: UDAH CHECKED TAPI SUPERVISOR GHOSTING (BELUM APPROVE)
        // =========================================================
        // Note: Variabel $fiveDaysAgo udah ada di atas, jadi kita tinggal pakai ulang

        // Cari status yang checked_date-nya 5 hari lalu, tapi approved_date-nya masih NULL
        $pendingApproveH5 = ApprovalStatus::whereNotNull('checked_date')
            ->whereNull('approved_date')
            ->whereDate('checked_date', $fiveDaysAgo)
            ->with(['project.customer'])
            ->get();

        foreach ($pendingApproveH5 as $status) {
            $project = $status->project;

            // Validasi data biar script tetep waras
            if (! $project || ! $project->customer) {
                continue;
            }

            $customerName = $project->customer->name;

            // Target korban: Management (Bos Besar yang bakal nge-ping Supervisor)
            // Management biasanya global, jadi gak usah difilter pake $deptId
            $managements = User::getManagement();

            // Kalo dapet nomor WA Management, sikatttt! 🚀
            if ($managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "Schedule sudah di-checked.\n\n" .
                    "Mohon diberitahukan kepada supervisor untuk segera *approve* schedule yang telah dibuat.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "Schedule sudah di-checked.\n\n" .
                    "Mohon diberitahukan kepada supervisor untuk segera *approve* schedule yang telah dibuat di *aplikasi new project CAR*.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $managements;
                $targetCc = collect(); // Sesuai request: CC dikosongin aja!

                // 4. TEMBAK BATCH MASSAL DENGAN CHANNEL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Reminder Project {$project->model} - Supervisor Approval Needed", // Subject Email
                    $channel // 👈 Parameter ajaib yang misahin shift pagi (WA) & shift malem (Email)
                );
            }
        }

        // =========================================================
        // 4. REMINDER H+5: UDAH APPROVED TAPI MANAGEMENT LUPA KLIK (BELUM MGMT APPROVE)
        // =========================================================

        // Cari status yang approved_date-nya 5 hari lalu, tapi management_approved_date-nya masih NULL
        $pendingMgmtApproveH5 = ApprovalStatus::whereNotNull('approved_date')
            ->whereNull('management_approved_date')
            ->whereDate('approved_date', $fiveDaysAgo)
            ->with(['project.customer'])
            ->get();

        foreach ($pendingMgmtApproveH5 as $status) {
            $project = $status->project;

            // Validasi data biar script tetep aman sentosa
            if (! $project || ! $project->customer) {
                continue;
            }

            $customerName = $project->customer->name;

            // Target korban: Management (Ngirim reminder ke diri mereka sendiri wkwk)
            $managements = User::getManagement();

            // Kalo datanya dapet, let it fly! 🚀
            if ($managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "Schedule sudah di-approved.\n\n" .
                    "Mohon segera *disetujui* schedule yang telah dibuat, agar bisa dimulai new project ini.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Target Mass Production : {$project->masspro_target}\n" .
                    "Schedule sudah di-approved.\n\n" .
                    "Mohon segera *disetujui* schedule yang telah dibuat di *aplikasi new project CAR*, agar bisa dimulai new project ini.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $managements;
                $targetCc = collect(); // Sesuai request: CC Kosongin aja bosku!

                // 4. TEMBAK BATCH MASSAL DENGAN CHANNEL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Reminder Project {$project->model} - Management Approval Needed", // Subject Email
                    $channel // 👈 Si pembawa pesan yang ngebagi jalur pagi & malem
                );
            }
        }

        // =========================================================
        // 5. REMINDER H-5 DUE DATE: PIC BELUM UPLOAD DOKUMEN
        // =========================================================

        // Cari tanggal 5 hari ke depan dari hari ini
        $inFiveDays = Carbon::now()->addDays(5)->toDateString();

        // Cari dokumen yang due_date-nya 5 hari lagi, tapi actual_date masih NULL
        $pendingUploadHMin5 = ProjectDocument::whereNull('actual_date')
            ->whereDate('due_date', $inFiveDays)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($pendingUploadHMin5 as $doc) {
            $project = $doc->project;

            // Validasi data biar script aman dan gak error
            if (! $project || ! $project->customer || ! $project->customer->department_id) {
                continue;
            }

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            // Ambil nama dokumen, jaga-jaga kalau relasinya kosong
            $docName = $doc->documentType ? $doc->documentType->name : $doc->document_type_code;

            // Format Due Date jadi lebih manusiawi dibaca pake translatedFormat
            $dueDateStr = Carbon::parse($doc->due_date)->translatedFormat('d F Y');

            // Panggil semua kasta di departemen ini
            $pics = User::getPIC($deptId);
            $leaders = User::getLeader($deptId);
            $supervisors = User::getSupervisor($deptId);
            $managements = User::getManagement();

            // Kalo dapet datanya si PIC, lgsg gaskeun! 🚀
            if ($pics->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Due Date    : {$dueDateStr}\n\n" .
                    "Mohon segera diupload.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Due Date    : {$dueDateStr}\n\n" .
                    "Mohon segera diupload di *aplikasi new project CAR*.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $pics; // TO khusus korban utama: PIC
                // CC: Seluruh atasan (Leader, Supervisor, Management) di-merge
                $targetCc = collect($leaders)->merge($supervisors)->merge($managements)->unique('id');

                // 4. TEMBAK BATCH MASSAL DENGAN CHANNEL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Reminder Project {$project->model} - H-5 Due Date", // Subject Email
                    $channel // Penjaga pintu tol pagi & malam
                );
            }
        }

        // =========================================================
        // 6. REMINDER H-1 DUE DATE: PIC BELUM UPLOAD DOKUMEN (ESKALASI KE LEADER)
        // =========================================================

        // Cari tanggal BESOK
        $tomorrow = Carbon::now()->addDay()->toDateString();

        // Cari dokumen yang due_date-nya besok, tapi actual_date masih NULL (belum uplaod)
        $pendingUploadHMin1 = ProjectDocument::whereNull('actual_date')
            ->whereDate('due_date', $tomorrow)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($pendingUploadHMin1 as $doc) {
            $project = $doc->project;

            // Validasi data anti-error
            if (! $project || ! $project->customer || ! $project->customer->department_id) {
                continue;
            }

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            // Nama dokumen
            $docName = $doc->documentType ? $doc->documentType->name : $doc->document_type_code;

            // Format Due Date pake translatedFormat biar elegan
            $dueDateStr = Carbon::parse($doc->due_date)->translatedFormat('d F Y');

            // Panggil semua kasta!
            $leaders = User::getLeader($deptId);
            $supervisors = User::getSupervisor($deptId);
            $managements = User::getManagement();

            // Kalau dapet Leader-nya, langsung bombardir (dan pastikan gak nyasar)! 🚀
            if ($leaders->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "*REMINDER*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Due Date    : {$dueDateStr}\n\n" .
                    "*PALING LAMBAT BESOK HARUS UPLOAD*\n\n" .
                    "Mohon diingatkan kepada PIC.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "*REMINDER*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Due Date    : {$dueDateStr}\n\n" .
                    "*PALING LAMBAT BESOK HARUS UPLOAD DI APLIKASI NEW PROJECT CAR*\n\n" .
                    "Mohon diingatkan kepada PIC.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $leaders; // TO Khusus Leader yang harus marahin PIC
                $targetCc = collect($supervisors)->merge($managements)->unique('id'); // Tembusan ke atasan

                // 4. TEMBAK BATCH MASSAL DENGAN CHANNEL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Reminder Project {$project->model} - H-1 Deadline Warning", // Subject Email
                    $channel // Penjaga Pintu WA & Email
                );
            }
        }

        // =========================================================
        // 7. REMINDER H+5 ACTUAL DATE: UDAH UPLOAD TAPI LEADER GHOSTING (BELUM CHECK)
        // =========================================================

        // Catatan: Variabel $fiveDaysAgo udah ada di atas, kita tinggal pakai ulang aja
        // Cari dokumen yang actual_date-nya pas 5 hari lalu, tapi checked_date masih NULL
        $pendingDocCheckH5 = ProjectDocument::whereNotNull('actual_date')
            ->whereNull('checked_date')
            ->whereDate('actual_date', $fiveDaysAgo)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($pendingDocCheckH5 as $doc) {
            $project = $doc->project;

            // Validasi data anti-error club
            if (! $project || ! $project->customer || ! $project->customer->department_id) {
                continue;
            }

            $deptId = $project->customer->department_id;
            $customerName = $project->customer->name;

            // Ambil nama dokumen
            $docName = $doc->documentType ? $doc->documentType->name : $doc->document_type_code;

            // Target korban SP lisan: SUPERVISOR (buat ngingetin Leader), Tembusan: MANAGEMENT
            $supervisors = User::getSupervisor($deptId);
            $managements = User::getManagement();

            // Kalau dapet nomor target, langsung bombardir! 🚀
            if ($supervisors->isNotEmpty() || $managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Status          : Waiting for Leader to Check\n\n" .
                    "Mohon diberitahukan kepada leader untuk segera *CHECK* Document yang sudah di-upload.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Status          : Waiting for Leader to Check\n\n" .
                    "Mohon diberitahukan kepada leader untuk segera *CHECK* Document yang sudah di-upload di *aplikasi new project CAR*.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $supervisors;
                $targetCc = $managements; // Bos besar masuk CC biar ikut mantau

                // 4. TEMBAK BATCH MASSAL DENGAN CHANNEL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Reminder Project {$project->model} - Document Check Needed", // Subject Email
                    $channel // Penjaga gate yang misahin email sama WA
                );
            }
        }

        // =========================================================
        // 8. REMINDER H+5 CHECKED DATE: UDAH CHECKED TAPI SPV GHOSTING (BELUM APPROVE)
        // =========================================================

        // Note: Variabel $fiveDaysAgo masih kita pakai dari yang di atas
        // Cari dokumen yang checked_date-nya pas 5 hari lalu, tapi approved_date masih NULL
        $pendingDocApproveH5 = ProjectDocument::whereNotNull('checked_date')
            ->whereNull('approved_date')
            ->whereDate('checked_date', $fiveDaysAgo)
            ->with(['project.customer', 'documentType'])
            ->get();

        foreach ($pendingDocApproveH5 as $doc) {
            $project = $doc->project;

            // Validasi anti-error (Management biasanya ga butuh $deptId, tp nama customer tetep butuh)
            if (! $project || ! $project->customer) {
                continue;
            }

            $customerName = $project->customer->name;

            // Ambil nama dokumen
            $docName = $doc->documentType ? $doc->documentType->name : $doc->document_type_code;

            // Target korban eskalasi: MANAGEMENT (Ngadu ke Bos Besar)
            $managements = User::getManagement();

            // Kalau datanya dapet, langsung luncurkan rudalnya! 🚀
            if ($managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Status          : Waiting for Supervisor to Approve\n\n" .
                    "Mohon diberitahukan kepada Supervisor untuk segera *APPROVE* Document yang sudah di-check.\n\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n" .
                    "Doc Name  : {$docName}\n" .
                    "Status          : Waiting for Supervisor to Approve\n\n" .
                    "Mohon diberitahukan kepada Supervisor untuk segera *APPROVE* Document yang sudah di-check di *aplikasi new project CAR*.\n\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $managements;
                $targetCc = collect(); // Sesuai request: CC kosongin aja biar eksklusif buat bos!

                // 4. TEMBAK BATCH MASSAL DENGAN CHANNEL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Reminder Project {$project->model} - Supervisor Approval Needed", // Subject Email
                    $channel // Penjaga palang pintu pagi & malem
                );
            }
        }

        // =========================================================
        // 9. REMINDER H+5 SEMUA DOC APPROVED: LEADER BELUM CHECK ONGOING PROJECT
        // =========================================================

        // Note: $fiveDaysAgo tetep pake variabel yang sama di atas
        // Cari project yang SEMUA dokumennya udah di-approve,
        // tapi ongoing_checked_date di tabel approval_statuses masih NULL.
        $pendingOngoingCheck = Project::whereHas('approvalStatus', function ($query) {
            $query->whereNull('ongoing_checked_date');
        })
            ->has('documents') // Pastiin project-nya beneran punya dokumen
            ->whereDoesntHave('documents', function ($query) {
                $query->whereNull('approved_date'); // Pastiin gak ada satupun doc yg belum di-approve
            })
            ->with(['customer', 'documents', 'approvalStatus'])
            ->get();

        foreach ($pendingOngoingCheck as $project) {
            // Cari tanggal approve paling terakhir (paling baru) dari tumpukan dokumen project ini
            $latestApproveDate = $project->documents->max('approved_date');

            // Cek apakah tanggal approve terakhir itu jatuhnya pas 5 hari yang lalu
            if (Carbon::parse($latestApproveDate)->toDateString() === $fiveDaysAgo) {

                // Validasi anti-error club
                if (! $project->customer || ! $project->customer->department_id) {
                    continue;
                }

                $deptId = $project->customer->department_id;
                $customerName = $project->customer->name;

                // Target korban SP lisan: SUPERVISOR (buat ngingetin Leader), Tembusan: MANAGEMENT
                $supervisors = User::getSupervisor($deptId);
                $managements = User::getManagement();

                // Kalau target dapet, langsung gaskan! 🚀
                if ($supervisors->isNotEmpty() || $managements->isNotEmpty()) {

                    // 1. PESAN WA (Format Original)
                    $msgWa = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                        "New Project For {$customerName} Project {$project->model}\n" .
                        "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                        "Semua document sudah diupload sesuai schedule.\n\n" .
                        "Mohon diberitahukan kepada leader untuk segera di-check agar bisa segera masspro.\n" .
                        "Terima kasih.";

                    // 2. PESAN EMAIL (Format Baru Request Klien)
                    $msgEmail = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                        "New Project For {$customerName} Project {$project->model}\n" .
                        "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                        "Semua document sudah diupload sesuai schedule.\n\n" .
                        "Mohon diberitahukan kepada leader untuk segera di-check di *aplikasi new project CAR*.\n" .
                        "Terima kasih.";

                    // 3. MAPPING TO & CC
                    $targetTo = $supervisors;
                    $targetCc = $managements; // Masukin Management ke jalur VIP (CC)

                    // 4. TEMBAK BATCH MASSAL DENGAN CHANNEL! 💥
                    BroadcastService::sendBatch(
                        $targetTo,
                        $targetCc,
                        $msgWa,
                        $msgEmail,
                        "Reminder Project {$project->model} - Leader Check Needed", // Subject Email
                        $channel // Parameter ajaib pengatur shift pengiriman
                    );
                }
            }
        }

        // =========================================================
        // 10. REMINDER H+5 ONGOING CHECKED: SPV GHOSTING (BELUM APPROVE ONGOING)
        // =========================================================

        // Note: Variabel $fiveDaysAgo masih kita pakai
        // Cari status yang ongoing_checked_date-nya pas 5 hari lalu, tapi ongoing_approved_date masih NULL
        $pendingOngoingApproveH5 = ApprovalStatus::whereNotNull('ongoing_checked_date')
            ->whereNull('ongoing_approved_date')
            ->whereDate('ongoing_checked_date', $fiveDaysAgo)
            ->with(['project.customer'])
            ->get();

        foreach ($pendingOngoingApproveH5 as $status) {
            $project = $status->project;

            // Validasi data anti-error (Management cuma butuh nama customer)
            if (! $project || ! $project->customer) {
                continue;
            }

            $customerName = $project->customer->name;

            // Target korban eskalasi: MANAGEMENT (Ngadu ke Bos Besar)
            $managements = User::getManagement();

            // Kalau datanya dapet, langsung luncurkan misilnya! 🚀
            if ($managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                    "Semua document sudah di-check.\n\n" .
                    "Mohon diberitahukan kepada supervisor untuk segera di-approve agar bisa segera masspro.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                    "Semua document sudah di-check.\n\n" .
                    "Mohon diberitahukan kepada supervisor untuk segera di-approve di *aplikasi new project CAR*.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $managements;
                $targetCc = collect(); // Sesuai request: CC dikosongin aja bosku!

                // 4. TEMBAK BATCH MASSAL DENGAN CHANNEL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Reminder Project {$project->model} - Supervisor Approval Needed", // Subject Email
                    $channel // Penjaga palang pintu WA & Email
                );
            }
        }

        // =========================================================
        // 11. REMINDER H+5 ONGOING APPROVED: MANAGEMENT LUPA FINAL APPROVE
        // =========================================================

        // Note: Variabel $fiveDaysAgo masih setia kita pakai
        // Cari status yang ongoing_approved_date-nya pas 5 hari lalu, tapi ongoing_management_approved_date masih NULL
        $pendingOngoingMgmtApproveH5 = ApprovalStatus::whereNotNull('ongoing_approved_date')
            ->whereNull('ongoing_management_approved_date')
            ->whereDate('ongoing_approved_date', $fiveDaysAgo)
            ->with(['project.customer'])
            ->get();

        foreach ($pendingOngoingMgmtApproveH5 as $status) {
            $project = $status->project;

            // Validasi data anti-error
            if (! $project || ! $project->customer) {
                continue;
            }

            $customerName = $project->customer->name;

            // Target penerima: MANAGEMENT (Sistem auto-savage ngingetin Bos)
            $managements = User::getManagement();

            // Kalau datanya dapet, langsung luncurkan notif pamungkasnya! 🚀
            if ($managements->isNotEmpty()) {

                // 1. PESAN WA (Format Original)
                $msgWa = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                    "Semua document sudah di-approve oleh supervisor.\n\n" .
                    "Mohon segera di-approve by management agar bisa segera masspro.\n" .
                    "Terima kasih.";

                // 2. PESAN EMAIL (Format Baru Request Klien)
                $msgEmail = "*REMINDER SUDAH LEWAT DUE DATE*\n" .
                    "New Project For {$customerName} Project {$project->model}\n" .
                    "{$project->part_number} - {$project->part_name} - Suffix {$project->suffix}\n\n" .
                    "Semua document sudah di-approve oleh supervisor.\n\n" .
                    "Mohon segera di-approve by management di *aplikasi new project CAR*.\n" .
                    "Terima kasih.";

                // 3. MAPPING TO & CC
                $targetTo = $managements;
                $targetCc = collect(); // Sesuai request: CC dikosongin!

                // 4. TEMBAK BATCH MASSAL DENGAN CHANNEL! 💥
                BroadcastService::sendBatch(
                    $targetTo,
                    $targetCc,
                    $msgWa,
                    $msgEmail,
                    "Reminder Project {$project->model} - Final Management Approval Needed", // Subject Email
                    $channel // Penjaga gawang terakhir
                );
            }
        }

        $this->info('Cek Beres!');
    }
}
