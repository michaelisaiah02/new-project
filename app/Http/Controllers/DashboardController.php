<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
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
