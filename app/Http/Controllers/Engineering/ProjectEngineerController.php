<?php

namespace App\Http\Controllers\Engineering;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\CustomerStage;
use App\Models\ProjectDocument;
use App\Http\Controllers\Controller;

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
                !isset($docGroups[$stageNumber]) ||
                empty($docGroups[$stageNumber])
            ) {
                $errors["documents_codes.$stageNumber"] =
                    "Stage {$stageNumber} must have at least 1 document.";
            }
        }

        if (!empty($errors)) {
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
                    'document_type_code'  => $docCode,
                    'customer_stage_id'   => $stage->id,
                ]);
            }
        }

        return redirect()->route('engineering.projects.assignDueDates', [
            'project' => $project->part_number
        ])->with('success', 'Documents updated successfully.');
    }

    public function assignDueDates(Project $project)
    {
        $projectDocuments = ProjectDocument::with([
            'stage:id,stage_number',
            'documentType:code,name'
        ])
            ->where('project_part_number', $project->part_number)
            ->orderBy('customer_stage_id')
            ->get()
            ->groupBy('customer_stage_id');

        return view('engineering.projects.assign-due-dates', compact(
            'project',
            'projectDocuments'
        ));
    }

    public function saveAssignDueDates(Request $request, Project $project)
    {
        $dueDates = $request->input('due_dates', []);

        if (empty($dueDates)) {
            return back()->with('error', 'No due dates submitted.');
        }

        foreach ($dueDates as $projectDocumentId => $date) {
            ProjectDocument::where('id', $projectDocumentId)
                ->where('project_part_number', $project->part_number)
                ->update([
                    'due_date' => $date ?: null,
                ]);
        }

        return redirect()
            ->route('engineering.projects.detail', ['project' => $project->part_number])
            ->with('success', 'Due dates assigned successfully.');
    }
}
