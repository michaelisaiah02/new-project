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
        // 1. Ambil semua data customer untuk pilihan di dropdown (selalu diambil)
        $customers = Customer::orderBy('name')->get();

        // 2. Cek apakah ada filter yang diisi oleh user
        $hasFilter = $request->filled('customer') ||
            $request->filled('model') ||
            $request->filled('part_number') ||
            $request->filled('suffix') ||
            $request->filled('minor_change') ||
            $request->filled('remark');

        // 3. Logika pengambilan data records
        if ($hasFilter) {
            // Jika ada filter, jalankan query
            $massproRecords = Project::query()
                ->when($request->customer, function ($query, $customer) {
                    return $query->where('customer_code', $customer);
                })
                ->when($request->model, function ($query, $model) {
                    return $query->where('model', 'like', '%' . $model . '%');
                })
                ->when($request->part_number, function ($query, $partNumber) {
                    return $query->where('part_number', 'like', '%' . $partNumber . '%');
                })
                ->when($request->suffix, function ($query, $suffix) {
                    return $query->where('suffix', 'like', '%' . $suffix . '%');
                })
                ->when($request->minor_change, function ($query, $minorChange) {
                    return $query->where('minor_change', 'like', '%' . $minorChange . '%');
                })
                ->when($request->remark, function ($query, $remark) {
                    if ($remark === 'all') {
                        $remark = ['completed', 'canceled'];
                    }
                    return $query->whereIn('remark', (array) $remark);
                })
                ->orderBy('customer_code')
                ->get();
        } else {
            // Jika tidak ada filter (awal buka halaman), kirim koleksi kosong
            $massproRecords = collect();
        }

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
