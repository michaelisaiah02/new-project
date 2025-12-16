<?php

namespace App\Http\Controllers\Marketing;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;

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
        $validated = $request->validate(
            [
                'customer_code' => 'required|string|max:10',
                'model' => 'required|string|max:50',
                'part_number' => 'required|string|max:50|unique:projects,part_number',
                'part_name' => 'required|string|max:100',
                'part_type' => 'required|string|in:Hose,Molding,Weatherstrip,Bonding Metal',
                'drawing_2d' => 'required|file|max:5120',
                'drawing_label_2d' => 'required|string|max:100',
                'drawing_3d' => 'nullable|file|max:5120',
                'drawing_label_3d' => 'nullable|string|max:100',
                'qty' => 'required|integer|min:1',
                'eee_number' => 'required|string|max:50',
                'suffix' => 'required|string|max:20',
                'drawing_number' => 'required|string|max:50',
                'drawing_revision_date' => 'required|date',
                'material_on_drawing' => 'required|string|max:255',
                'receive_date_sldg' => 'required|date',
                'sldg_number' => 'required|string|max:50',
                'masspro_target' => 'required|date',
                'minor_change' => 'required|string',
            ],
            [
                'drawing_2d.required' => 'Masukan kembali File Drawing 2D.',
                'drawing_2d.max' => 'Ukuran File Drawing 2D maksimal 5MB.',
                'drawing_3d.max' => 'Ukuran File Drawing 3D maksimal 5MB.',
                'part_number.unique' => 'Sudah ada Proyek dengan part yang sama di dalam database.',
                'qty.min' => 'Quantity minimal adalah 1.',
            ]
        );

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
