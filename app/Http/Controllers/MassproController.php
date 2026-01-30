<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\ProjectDocument;
use Illuminate\Support\Facades\DB;

class MassproController extends Controller
{
    public function index(Request $request)
    {
        // 1. CEK: Apakah halaman baru dibuka (tanpa filter)?
        // Kita anggap 'remark' default 'all' tidak dihitung sebagai filter aktif user
        $filters = $request->only(['customer', 'model', 'part_number', 'suffix', 'minor_change']);
        $hasFilter = collect($filters)->filter()->isNotEmpty();

        // Jika tidak ada filter, kirim data kosong & customer list saja
        if (!$hasFilter) {
            return view('masspro.index', [
                'customers' => Customer::orderBy('name')->get(),
                'massproRecords' => collect(), // Kosong
                'autoFilled' => [] // Tidak ada auto-fill
            ]);
        }

        // 2. QUERY UTAMA
        $query = Project::query()
            ->whereIn('remark', ['completed', 'canceled']); // Base Filter

        // Filter Independen (Bisa diisi acak)
        if ($request->filled('customer')) {
            $query->where('customer_code', $request->customer);
        }
        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }
        if ($request->filled('part_number')) {
            $query->where('part_number', $request->part_number);
        }
        if ($request->filled('suffix')) {
            $query->where('suffix', $request->suffix);
        }
        if ($request->filled('minor_change')) {
            $query->where('minor_change', $request->minor_change);
        }

        // Filter Remark
        if ($request->filled('remark') && $request->remark != 'all') {
            $query->where('remark', $request->remark);
        }

        $massproRecords = $query->orderBy('updated_at', 'desc')->get();

        // 3. LOGIKA "OTOMATIS PILIHAN ATASANNYA" (Reverse Lookup)
        // Jika user memilih Part Number tapi Customer/Model kosong, kita cari induknya
        $autoFilled = [];
        if ($request->filled('part_number') && (!$request->filled('customer') || !$request->filled('model'))) {
            // Ambil data project pertama yg cocok untuk cari induknya
            $parent = $massproRecords->first();
            if ($parent) {
                if (!$request->filled('customer')) $autoFilled['customer'] = $parent->customer_code;
                if (!$request->filled('model'))    $autoFilled['model'] = $parent->model;
            }
        }

        $customers = Customer::orderBy('name')->get();

        return view('masspro.index', compact('customers', 'massproRecords', 'autoFilled'));
    }

    // --- API METHODS (Update agar menerima semua parameter) ---
    public function getModels(Request $request)
    {
        $q = Project::select('model')->distinct()->whereIn('remark', ['completed', 'canceled']);
        // Filter opsional (kalau ada input lain, filter ini dipersempit)
        if ($request->filled('customer_code')) $q->where('customer_code', $request->customer_code);
        if ($request->filled('part_number'))   $q->where('part_number', $request->part_number);
        return response()->json($q->orderBy('model')->pluck('model')); // Pluck biar jadi array string simple
    }

    public function getParts(Request $request)
    {
        $q = Project::select('part_number', 'part_name')->distinct()->whereIn('remark', ['completed', 'canceled']);
        if ($request->filled('customer_code')) $q->where('customer_code', $request->customer_code);
        if ($request->filled('model'))         $q->where('model', $request->model);
        return response()->json($q->orderBy('part_number')->get());
    }

    public function getSuffixes(Request $request)
    {
        $q = Project::select('suffix')->distinct()->whereNotNull('suffix')->where('suffix', '!=', '')->whereIn('remark', ['completed', 'canceled']);
        if ($request->filled('part_number')) $q->where('part_number', $request->part_number);
        return response()->json($q->orderBy('suffix')->pluck('suffix'));
    }

    public function getMinorChanges(Request $request)
    {
        $q = Project::select('minor_change')->distinct()->whereNotNull('minor_change')->where('minor_change', '!=', '')->whereIn('remark', ['completed', 'canceled']);
        if ($request->filled('part_number')) $q->where('part_number', $request->part_number);
        return response()->json($q->orderBy('minor_change')->pluck('minor_change'));
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
