<?php

namespace App\Http\Controllers\Marketing;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
        /**
         * ==========================
         * 1. HANDLE TEMP UPLOAD
         * ==========================
         */
        if ($request->hasFile('drawing_2d') && $request->file('drawing_2d')->getSize() <= 5242880) {
            $file = $request->file('drawing_2d');
            $ext = strtolower($file->getClientOriginalExtension());
            $label = $request->input('drawing_label_2d');

            // Jika input label tidak punya ekstensi, atau ekstensinya masih .tif
            // Kita paksa ubah labelnya menjadi .png jika itu file TIFF
            if (in_array($ext, ['tif', 'tiff'])) {
                $label = pathinfo($label, PATHINFO_FILENAME) . '.png';
            }

            // Hapus temp lama jika ada
            if (session()->has('drawing_2d_temp')) {
                Storage::disk('public')->delete(session('drawing_2d_temp'));
            }

            session([
                'drawing_2d_temp' => FileHelper::storeTempDrawing($file),
                'drawing_2d_name' => $label, // Gunakan label yang sudah disesuaikan ekstensinya
            ]);
        }

        if ($request->hasFile('drawing_3d')) {

            if (session()->has('drawing_3d_temp')) {
                Storage::disk('public')->delete(session('drawing_3d_temp'));
            }

            session([
                'drawing_3d_temp' => FileHelper::storeTempDrawing($request->file('drawing_3d')),
                'drawing_3d_name' => $request->input('drawing_label_3d'),
            ]);
        }

        /**
         * ==========================
         * 2. VALIDATION
         * ==========================
         */
        $validated = $request->validate(
            [
                'customer_code' => 'required|string|max:10',
                'model' => 'required|string|max:50',

                'part_number' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('projects')->where(
                        fn($q) => $q->where('suffix', $request->suffix)
                            ->where('minor_change', $request->minor_change)
                    ),
                ],

                'part_name' => 'required|string|max:100',
                'part_type' => 'required|in:Hose,Molding,Weatherstrip,Bonding Metal',

                'drawing_2d' => 'required|file|mimes:pdf|max:5120',
                'drawing_label_2d' => 'required|string|max:100',

                // 🔥 FILE 3D TIDAK REQUIRED LAGI
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
                'part_number.unique' => 'Part number dengan suffix dan minor change yang sama sudah ada.',
                'drawing_2d.max' => 'Ukuran File Drawing 2D maksimal 5MB.',
                'drawing_3d.max' => 'Ukuran File Drawing 3D maksimal 5MB.',
            ]
        );

        /**
         * ==========================
         * 3. NORMALISASI
         * ==========================
         */
        $validated['model'] = strtoupper($validated['model']);
        $validated['part_number'] = strtoupper($validated['part_number']);
        $validated['part_name'] = ucwords(strtolower($validated['part_name']));
        $validated['customer_name_snapshot'] = Customer::where('code', $validated['customer_code'])
            ->value('name');
        $validated['created_by'] = auth()->id();

        /**
         * ==========================
         * 4. MOVE TEMP → FINAL
         * ==========================
         */
        if (session()->has('drawing_2d_temp')) {
            $validated['drawing_2d'] = FileHelper::moveTempToFinal(
                session('drawing_2d_temp'),
                $validated['customer_code'],
                $validated['model'],
                $validated['part_number'],
                session('drawing_2d_name')
            );
        }

        if (session()->has('drawing_3d_temp')) {
            $validated['drawing_3d'] = FileHelper::moveTempToFinal(
                session('drawing_3d_temp'),
                $validated['customer_code'],
                $validated['model'],
                $validated['part_number'],
                session('drawing_3d_name')
            );
        }

        session()->forget([
            'drawing_2d_temp',
            'drawing_2d_name',
            'drawing_3d_temp',
            'drawing_3d_name',
        ]);

        /**
         * ==========================
         * 5. SAVE
         * ==========================
         */
        Project::create($validated);

        return back()->with('success', 'New project created successfully.');
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
