<?php

namespace App\Http\Controllers\Marketing;

use App\Models\Customer;
use App\Models\NewProject;
use Illuminate\Http\Request;
use Pest\ArchPresets\Custom;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewProjectRequest;
use App\Http\Requests\UpdateNewProjectRequest;

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_code' => 'required|string|max:10',
            'model' => 'required|string|max:50',
            'part_number' => 'required|string|max:50',
            'part_name' => 'required|string|max:100',
            'part_type' => 'required|string|max:50',
            'drawing_2d' => 'required|file|mimes:pdf,dwg,dxf',
            'drawing_label_2d' => 'required|string|max:100',
            'drawing_3d' => 'required|file|mimes:pdf,step,iges,stp',
            'drawing_label_3d' => 'required|string|max:100',
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

        $customerCode = $validated['customer_code'];

        // === 2D FILE ===
        if ($request->hasFile('drawing_2d')) {

            // Ambil label (misal: ABC123-2D)
            $name2d = $validated['drawing_label_2d']
                ? $validated['drawing_label_2d'] . '.pdf'
                : $request->file('drawing_2d')->getClientOriginalName();

            // Path tujuan
            $path2d = $request->file('drawing_2d')->storeAs(
                "{$customerCode}/drawings",
                $name2d,
                'public'
            );

            $validated['drawing_2d'] = $path2d;
            unset($validated['drawing_label_2d']);
        }

        // === 3D FILE ===
        if ($request->hasFile('drawing_3d')) {

            $name3d = $validated['drawing_label_3d']
                ? $validated['drawing_label_3d'] . '.pdf'
                : $request->file('drawing_3d')->getClientOriginalName();

            $path3d = $request->file('drawing_3d')->storeAs(
                "{$customerCode}/drawings",
                $name3d,
                'public'
            );

            $validated['drawing_3d'] = $path3d;
            unset($validated['drawing_label_3d']);
        }

        NewProject::create($validated);

        return redirect()->back()->with('success', 'New project created successfully.');
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
