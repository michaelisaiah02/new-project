<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;

class MassproController extends Controller
{
    public function index(Request $request)
    {
        // ambil data berdasarkan filter jika ada kalau tidak adak ada ambil semua
        $query = Project::query();
        if ($request->filled('customer_code')) {
            $query->where('customer_code', $request->input('customer_code'));
        }
        if ($request->filled('model')) {
            $query->where('model', 'like', '%'.$request->input('model').'%');
        }
        if ($request->filled('part_number')) {
            $query->where('part_number', 'like', '%'.$request->input('part_number').'%');
        }
        if ($request->filled('suffix')) {
            $query->where('suffix', 'like', '%'.$request->input('suffix').'%');
        }
        $massproRecords = $query->where('remark', 'completed')->get();

        $customers = Customer::orderBy('name')->get();

        return view('masspro.index', compact('customers', 'massproRecords'));
    }

    public function view(Project $project)
    {
        $projectDocuments = ProjectDocument::with([
            'stage:id,stage_number',
            'documentType:code,name',
        ])
            ->where('project_id', $project->id)
            ->orderBy('customer_stage_id')
            ->get()
            ->groupBy('customer_stage_id');

        return view('masspro.view', compact('project', 'projectDocuments'));
    }

    public function document(ProjectDocument $projectDocument)
    {
        return view('masspro.document', compact('projectDocument'));
    }
}
