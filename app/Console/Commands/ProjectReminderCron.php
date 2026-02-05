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
    protected $description = 'Kirim WA reminder buat project macet atau due date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Jalanin Project Reminder...');

        // === POIN 2: 3 Hari belum checked/approved ===
        $projects3Days = Project::whereIn('remark', ['new', 'not checked', 'not approved'])
            ->whereDate('created_at', Carbon::now()->subDays(3)->toDateString())
            ->get();

        foreach ($projects3Days as $p) {
            $targets = implode(',', [User::getCheckerNumbers(), User::getApproverNumbers(), User::getManagementNumbers()]);
            FonnteService::send($targets, "⚠️ *Reminder 3 Hari*\n\nProject {$p->part_name} belum diproses selama 3 hari.");
        }

        // === POIN 3: 7 Hari belum checked/approved ===
        $projects7Days = Project::whereIn('remark', ['new', 'not checked', 'not approved'])
            ->whereDate('created_at', Carbon::now()->subDays(7)->toDateString())
            ->get();

        foreach ($projects7Days as $p) {
            $targets = implode(',', [User::getCheckerNumbers(), User::getApproverNumbers(), User::getManagementNumbers()]);
            FonnteService::send($targets, "🚨 *URGENT 7 Hari*\n\nProject {$p->part_name} MACET 7 hari. Tolong segera di-follow up!");
        }

        // === POIN 5: H-5 Due Date Document ===
        // Cari dokumen yg actual_date nya null DAN due_datenya 5 hari lagi
        $docsH5 = ProjectDocument::whereNull('actual_date')
            ->whereDate('due_date', Carbon::now()->addDays(5)->toDateString())
            ->with('project') // Eager load project
            ->get();

        foreach ($docsH5 as $doc) {
            $targets = implode(',', [User::getApproverNumbers(), User::getCheckerNumbers(), User::getEngineeringNumbers()]);
            FonnteService::send($targets, "⏳ *Reminder H-5 Due Date*\n\nDokumen: {$doc->document_type_code}\nProject: {$doc->project->part_name}\nDue Date: {$doc->due_date}");
        }

        // === POIN 6: Overdue Document (Lewat Due Date) ===
        // Cari yg belum selesai DAN due_date nya kemarin (biar ga spam tiap hari, kirim pas H+1 aja)
        $docsOverdue = ProjectDocument::whereNull('actual_date')
            ->whereDate('due_date', '<', Carbon::now()->toDateString())
            // Logic tambahan: biar ga dikirim tiap hari selamanya, mungkin cek yg due_datenya "kemarin"
            ->whereDate('due_date', Carbon::now()->subDay()->toDateString())
            ->with('project')
            ->get();

        foreach ($docsOverdue as $doc) {
            $targets = implode(',', [User::getApproverNumbers(), User::getCheckerNumbers(), User::getManagementNumbers()]);
            FonnteService::send($targets, "🔥 *PROJECT OVERDUE*\n\nDokumen: {$doc->document_type_code}\nProject: {$doc->project->part_name}\nSudah lewat due date! Mohon eskalasi.");
        }

        $this->info('Reminder kelar dikirim bro!');
    }
}
