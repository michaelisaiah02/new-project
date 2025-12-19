<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;
use Pest\ArchPresets\Custom;

class MassproController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::all();
        $models = Project::select('model')
            ->distinct()
            ->orderBy('model')
            ->get();
        $partNumbers = Project::select('part_number')
            ->distinct()
            ->orderBy('part_number')
            ->get();
        $suffixes = Project::select('suffix')
            ->distinct()
            ->orderBy('suffix')
            ->get();


        return view('masspro.index', compact('customers', 'models', 'partNumbers', 'suffixes'));
    }

    public function massproView(Project $project)
    {
        return view('masspro.view', compact('project'));
    }
}
