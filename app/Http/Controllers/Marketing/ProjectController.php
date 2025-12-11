<?php

namespace App\Http\Controllers\Marketing;

use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;
use Pest\ArchPresets\Custom;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Helpers\FileHelper;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        return view('marketing.projects.index', compact('customers'));
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_code' => 'required|string|max:10',
            'model' => 'required|string|max:50',
            'part_number' => 'required|string|max:50',
            'part_name' => 'required|string|max:100',
            'part_type' => 'required|string|in:Hose,Molding,Weatherstrip,Bonding Metal',
            'drawing_2d' => 'required|file',
            'drawing_label_2d' => 'required|string|max:100',
            'drawing_3d' => 'nullable|file',
            'drawing_label_3d' => 'nullable|string|max:100',
            'qty' => 'required|integer|min:1',
            'eee_number' => 'nullable|string|max:50',
            'drawing_number' => 'nullable|string|max:50',
            'drawing_revision_date' => 'nullable|date',
            'material_on_drawing' => 'nullable|string|max:255',
            'receive_date_sldg' => 'nullable|date',
            'sldg_number' => 'nullable|string|max:50',
            'masspro_target' => 'nullable|date',
            'message' => 'nullable|string',
            'minor_change' => 'nullable|string',
        ]);

        $validated['model'] = strtoupper($validated['model']);
        $validated['part_number'] = strtoupper($validated['part_number']);
        $validated['part_name'] = ucwords(strtolower($validated['part_name']));

        $customerCode = $validated['customer_code'];

        // === 2D FILE ===
        if ($request->hasFile('drawing_2d')) {

            $name2d = $validated['drawing_label_2d']
                ?: $request->file('drawing_2d')->getClientOriginalName();

            // Simpan file via helper
            $validated['drawing_2d'] = FileHelper::storeDrawingFile(
                $request->file('drawing_2d'),
                $customerCode,
                $validated['model'],
                $validated['part_number'],
                $name2d
            );

            unset($validated['drawing_label_2d']);
        }

        // === 3D FILE ===
        if ($request->hasFile('drawing_3d')) {

            $name3d = $validated['drawing_label_3d']
                ?: $request->file('drawing_3d')->getClientOriginalName();

            // Simpan file via helper
            $validated['drawing_3d'] = FileHelper::storeDrawingFile(
                $request->file('drawing_3d'),
                $customerCode,
                $validated['model'],
                $validated['part_number'],
                $name3d
            );

            unset($validated['drawing_label_3d']);
        }

        Project::create($validated);

        return redirect()->back()->with('success', 'New project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }
}
