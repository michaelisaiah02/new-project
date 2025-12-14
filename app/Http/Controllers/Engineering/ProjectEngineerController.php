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
        // Ambil semua stage + dokumen master
        $stages = $project->customer->stages()
            ->with(['documents' => function ($q) {
                $q->orderBy('name');
            }])
            ->orderBy('stage_number')
            ->get();

        // Ambil dokumen yang SUDAH dipilih project
        $selectedDocs = ProjectDocument::where('project_part_number', $project->part_number)
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

        foreach ($stages as $stage) {
            $stageNumber = $stage->stage_number;
            $selectedDocs = $docGroups[$stageNumber] ?? [];

            // Hapus semua dulu untuk stage ini
            ProjectDocument::where('project_part_number', $project->part_number)
                ->where('customer_stage_id', $stage->id)
                ->delete();

            // Insert ulang sesuai pilihan user
            foreach ($selectedDocs as $docCode) {
                ProjectDocument::create([
                    'project_part_number' => $project->part_number,
                    'document_type_code' => $docCode,
                    'customer_stage_id' => $stage->id,
                ]);
            }
        }

        ApprovalStatus::where('part_number', $project->part_number)->updateOrCreate([
            'part_number' => $project->part_number,
        ], [
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
        ]);

        Project::where('part_number', $project->part_number)
            ->update([
                'remark' => 'not checked',
            ]);

        return redirect()->route('engineering.projects.assignDueDates', [
            'project' => $project->part_number,
        ])->with('success', 'Documents updated successfully.');
    }

    public function assignDueDates(Project $project)
    {
        $projectDocuments = ProjectDocument::with([
            'stage:id,stage_number',
            'documentType:code,name',
        ])
            ->where('project_part_number', $project->part_number)
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

    public function updateDueDate(Request $request)
    {
        ProjectDocument::where('id', $request->project_document_id)
            ->update(['due_date' => $request->due_date]);

        return response()->json(['status' => 'success']);
    }

    public function approval(Request $request)
    {
        $status = ApprovalStatus::firstOrCreate([
            'part_number' => $request->project_part_number,
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

        Project::where('part_number', $request->project_part_number)
            ->update([
                'remark' => match ($request->action) {
                    'checked' => 'not approved',
                    'approved' => 'not approved management',
                    'approved_management' => 'approved',
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
            ->where('project_part_number', $project->part_number)
            ->orderBy('customer_stage_id')
            ->get()
            ->groupBy('customer_stage_id');

        return view('engineering.projects.ongoing', compact(
            'project',
            'projectDocuments'
        ));
    }
}
