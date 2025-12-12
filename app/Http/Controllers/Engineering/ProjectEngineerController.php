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
        $stages = $project->customer->stages()
            ->with(['documents' => function ($q) {
                $q->orderBy('name'); // optional biar rapi
            }])
            ->orderBy('stage_number')
            ->get();
        return view('engineering.projects.new', compact('project', 'stages'));
    }

    public function saveNew(Request $request, Project $project)
    {
        $docGroups = $request->input('documents_codes', []);

        if (empty($docGroups)) {
            return back()->with('error', 'Select at least 1 document.');
        }

        foreach ($docGroups as $stageNumber => $docs) {

            // ambil stage yg sesuai
            $stage = CustomerStage::where('customer_code', $project->customer_code)
                ->where('stage_number', $stageNumber)
                ->first();

            if (!$stage) continue;

            foreach ($docs as $docCode) {
                ProjectDocument::updateOrCreate([
                    'project_part_number'  => $project->part_number,
                    'document_type_code'   => $docCode,
                    'customer_stage_id'    => $stage->id,
                ]);
            }
        }

        return redirect()->route('engineering.projects.assignDueDates', [
            'project' => $project->part_number
        ]);
    }

    public function assignDueDates(Request $request, Project $project)
    {
        return view('engineering.projects.assign-due-dates', compact('project'));
    }
}
