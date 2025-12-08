<?php

namespace App\Http\Controllers;

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
        return view('engineering');
    }

    public function management()
    {
        return view('management');
    }
}
