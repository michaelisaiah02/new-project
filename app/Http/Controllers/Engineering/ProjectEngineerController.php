<?php

namespace App\Http\Controllers\Engineering;

use App\Http\Controllers\Controller;
use App\Models\ApprovalStatus;
use App\Models\CustomerStage;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;

use function Symfony\Component\Clock\now;

class ProjectEngineerController extends Controller
{
    public function new(Project $project)
    {
        if (auth()->user()->approved || auth()->user()->checked) {
            if (! $project->approvalStatus) {
                if (auth()->user()->approved) {
                    return redirect()->back()->with('error', 'Belum bisa approve karena proyek belum di-setup.');
                } else {
                    return redirect()->back()->with('error', 'Belum bisa check karena proyek belum di-setup.');
                }
            } else {
                return redirect()->route('engineering.projects.assignDueDates', [
                    'project' => $project->id,
                ]);
            }
        }
        // Ambil semua stage + dokumen master
        $stages = $project->customer->stages()
            ->with([
                'documents' => function ($q) {
                    $q->orderBy('name');
                },
            ])
            ->orderBy('stage_number')
            ->get();

        // Ambil dokumen yang SUDAH dipilih project
        $selectedDocs = ProjectDocument::where('project_id', $project->id)
            ->get()
            ->groupBy('customer_stage_id')
            ->map(function ($rows) {
                return $rows->pluck('document_type_code')->toArray();
            });

        return view('engineering.projects.new', compact(
            'project',
            'stages',
            'selectedDocs'
        ));
    }

    public function saveNew(Request $request, Project $project)
    {
        $docGroups = $request->input('documents_codes', []);

        // Ambil semua stage customer (yang memang ditampilkan di form)
        $stages = CustomerStage::where('customer_code', $project->customer_code)
            ->orderBy('stage_number')
            ->get();

        if ($stages->isEmpty()) {
            return back()->with('error', 'No stages defined for this customer.');
        }

        // 🔴 VALIDASI: setiap stage minimal 1 dokumen
        $errors = [];

        foreach ($stages as $stage) {
            $stageNumber = $stage->stage_number;

            if (
                ! isset($docGroups[$stageNumber]) ||
                empty($docGroups[$stageNumber])
            ) {
                $errors["documents_codes.$stageNumber"] =
                    "Stage {$stageNumber} must have at least 1 document.";
            }
        }

        if (! empty($errors)) {
            return back()
                ->withErrors($errors)
                ->withInput();
        }

        $hasChanges = false;

        foreach ($stages as $stage) {
            $stageNumber = $stage->stage_number;
            $selectedDocs = $docGroups[$stageNumber] ?? [];

            $existingDocs = ProjectDocument::where('project_id', $project->id)
                ->where('customer_stage_id', $stage->id)
                ->pluck('document_type_code')
                ->toArray();

            $toInsert = array_diff($selectedDocs, $existingDocs);
            $toDelete = array_diff($existingDocs, $selectedDocs);

            // 🔴 kalau ada perubahan → tandai
            if (! empty($toInsert) || ! empty($toDelete)) {
                $hasChanges = true;
            }

            // hapus
            if ($toDelete) {
                ProjectDocument::where('project_id', $project->id)
                    ->where('customer_stage_id', $stage->id)
                    ->whereIn('document_type_code', $toDelete)
                    ->delete();
            }

            // insert
            foreach ($toInsert as $docCode) {
                ProjectDocument::create([
                    'project_id' => $project->id,
                    'document_type_code' => $docCode,
                    'customer_stage_id' => $stage->id,
                ]);
            }
        }

        if ($hasChanges) {
            ApprovalStatus::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'created_by_id' => auth()->id(),
                    'created_by_name' => auth()->user()->name,
                    'created_date' => now(),
                    'checked_by_id' => null,
                    'checked_by_name' => null,
                    'checked_date' => null,
                    'approved_by_id' => null,
                    'approved_by_name' => null,
                    'approved_date' => null,
                    'management_approved_by_id' => null,
                    'management_approved_by_name' => null,
                    'management_approved_date' => null,
                ]
            );

            Project::find($project->id)
                ->update(['remark' => 'not checked']);
        }

        return redirect()->route('engineering.projects.assignDueDates', [
            'project' => $project->id,
        ])->with('success', 'Documents updated successfully.');
    }

    public function assignDueDates(Project $project)
    {
        $projectDocuments = ProjectDocument::with([
            'stage',
            'documentType:code,name',
        ])
            ->where('project_id', $project->id)
            ->orderBy('customer_stage_id')
            ->get()
            ->groupBy('customer_stage_id');

        $user = auth()->user();

        $canCheck = $user->checked === true && $project->approvalStatus->checked_date === null;
        $canApprove = $user->approved === true && auth()->user()->department->type() === 'engineering' && $project->approvalStatus->checked_date !== null
            && $project->approvalStatus->approved_date === null;
        $canApproveManagement = $user->approved === true && auth()->user()->department->type() === 'management' && $project->approvalStatus->approved_date !== null
            && $project->approvalStatus->management_approved_date === null;

        return view('engineering.projects.assign-due-dates', compact(
            'project',
            'projectDocuments',
            'canCheck',
            'canApprove',
            'canApproveManagement'
        ));
    }

    public function updateDueDates(Request $request, Project $project)
    {
        $request->validate([
            'due_dates.*' => 'nullable|date',
        ]);

        foreach ($request->due_dates ?? [] as $pdId => $date) {
            ProjectDocument::findOrFail($pdId)
                ->update([
                    'due_date' => $date,
                ]);
        }

        // 🔴 setelah save, status balik ke not checked
        $project->update([
            'remark' => 'not checked',
        ]);

        $project->approvalStatus->update([
            'checked_by_id' => null,
            'checked_by_name' => null,
            'checked_date' => null,
            'approved_by_id' => null,
            'approved_by_name' => null,
            'approved_date' => null,
            'management_approved_by_id' => null,
            'management_approved_by_name' => null,
            'management_approved_date' => null,
        ]);

        return redirect()
            ->route('engineering')
            ->with('success', 'Due dates saved successfully.');
    }

    public function approval(Request $request)
    {
        $status = ApprovalStatus::firstOrCreate([
            'project_id' => $request->project_id,
        ]);

        $user = auth()->user();

        if ($request->action === 'checked' && $user->checked) {
            $status->update([
                'checked_by_id' => $user->id,
                'checked_by_name' => $user->name,
                'checked_date' => now(),
            ]);
        }

        if ($request->action === 'approved' && $user->approved) {
            $status->update([
                'approved_by_id' => $user->id,
                'approved_by_name' => $user->name,
                'approved_date' => now(),
            ]);
        }

        if ($request->action === 'approved_management' && $user->approved) {
            $status->update([
                'management_approved_by_id' => $user->id,
                'management_approved_by_name' => $user->name,
                'management_approved_date' => now(),
            ]);
        }

        Project::findOrFail($request->project_id)
            ->update([
                'remark' => match ($request->action) {
                    'checked' => 'not approved',
                    'approved' => 'not approved management',
                    'approved_management' => 'on going',
                    default => 'new',
                },
            ]);

        return response()->json(['status' => 'success']);
    }

    public function updateToOnGoing(Project $project)
    {
        $project->update([
            'remark' => 'on going',
        ]);

        return redirect()
            ->route('engineering')
            ->with('success', 'Due dates assigned successfully.');
    }

    public function ongoing(Project $project)
    {
        $projectDocuments = ProjectDocument::with([
            'stage:id,stage_number',
            'documentType:code,name',
        ])
            ->where('project_id', $project->id)
            ->orderBy('customer_stage_id')
            ->get()
            ->groupBy('customer_stage_id');

        return view('engineering.projects.ongoing', compact(
            'project',
            'projectDocuments'
        ));
    }

    public function checkedOngoing(Project $project)
    {
        $project->approvalStatus->update([
            'ongoing_checked_by_id' => auth()->id(),
            'ongoing_checked_by_name' => auth()->user()->name,
            'ongoing_checked_date' => now(),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function approvedOngoing(Project $project)
    {
        if (auth()->user()->department->type() === 'management') {
            $project->approvalStatus->update([
                'ongoing_management_approved_by_id' => auth()->id(),
                'ongoing_management_approved_by_name' => auth()->user()->name,
                'ongoing_management_approved_date' => now(),
            ]);

            // Set project as completed
            $project->update([
                'remark' => 'completed',
            ]);

            return response()->json(['status' => 'success']);
        } else {
            $project->approvalStatus->update([
                'ongoing_approved_by_id' => auth()->id(),
                'ongoing_approved_by_name' => auth()->user()->name,
                'ongoing_approved_date' => now(),
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function cancel(Project $project)
    {
        $project->update([
            'remark' => 'cancelled',
        ]);

        return redirect()
            ->route('engineering')
            ->with('success', 'Project has been cancelled.');
    }
}
