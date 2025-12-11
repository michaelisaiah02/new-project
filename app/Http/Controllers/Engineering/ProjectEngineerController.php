<?php

namespace App\Http\Controllers\Engineering;

use App\Http\Controllers\Controller;
use App\Models\Project;

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
}
