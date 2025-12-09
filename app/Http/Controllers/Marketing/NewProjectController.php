<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewProjectRequest;
use App\Http\Requests\UpdateNewProjectRequest;
use App\Models\Customer;
use App\Models\NewProject;
use Pest\ArchPresets\Custom;

class NewProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        return view('marketing.new-projects.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNewProjectRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(NewProject $newProject)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NewProject $newProject)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNewProjectRequest $request, NewProject $newProject)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NewProject $newProject)
    {
        //
    }
}
