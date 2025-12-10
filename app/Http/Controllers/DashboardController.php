<?php

namespace App\Http\Controllers;

use App\Models\NewProject;

class DashboardController extends Controller
{
    public function index()
    {
        $type = auth()->user()->department->type();
        return match ($type) {
            'marketing' => redirect()->route('marketing'),
            'engineering' => redirect()->route('engineering'),
            'management' => redirect()->route('management'),
            default => abort(403, 'Unauthorized action.'),
        };
    }

    public function marketing()
    {
        return view('marketing');
    }

    public function engineering()
    {
        $newProjects = NewProject::with('customer')
            ->whereHas('customer', function ($q) {
                $q->where('department_id', auth()->user()->department_id);
            })->whereIn('remark', ['new', 'not checked', 'not approved'])
            ->get();

        $ongoingProjects = NewProject::with('customer')
            ->whereHas('customer', function ($q) {
                $q->where('department_id', auth()->user()->department_id);
            })->where('remark', 'on going')
            ->get();

        return view('engineering', compact('newProjects', 'ongoingProjects'));
    }

    public function management()
    {
        return view('management');
    }
}
