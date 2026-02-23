<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KPIController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::orderBy('name')->get();

        // Inisialisasi variabel kosong agar tidak error jika tidak ada pencarian
        $selectedProject = null;
        $chartLabels = [];
        $chartValues = [];
        $delayDocuments = collect(); // Koleksi kosong untuk daftar delay

        // Cek apakah user sudah melakukan pencarian lengkap
        if ($request->filled('part_number') && $request->filled('customer')) {

            $query = Project::query();
            $query->where('customer_code', $request->customer);
            $query->where('model', $request->model);
            $query->where('part_number', $request->part_number);

            // Filter Suffix & Minor Change (Sesuai kode sebelumnya)
            if ($request->filled('suffix')) {
                $query->where('suffix', $request->suffix);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('suffix')->orWhere('suffix', '');
                });
            }
            if ($request->filled('minor_change')) {
                $query->where('minor_change', $request->minor_change);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('minor_change')->orWhere('minor_change', '');
                });
            }

            // Ambil 1 Project beserta dokumen dan nama stage-nya
            // Kita urutkan dokumen berdasarkan stage id agar chart-nya urut (Step 1, Step 2, dst)
            $selectedProject = $query->with(['documents.stage', 'documents.documentType'])->first();

            if ($selectedProject) {
                // --- LOGIKA MENGHITUNG CHART ---

                // 1. Grouping dokumen berdasarkan Nama Stage
                $groupedDocs = $selectedProject->documents->sortBy('customer_stage_id')->groupBy(function ($doc) {
                    return $doc->stage->stage_number ?? 'Unknown Stage';
                });

                foreach ($groupedDocs as $stageName => $docs) {
                    // Sesuai request: Not Submitted (actual_date null) TIDAK DIHITUNG
                    $submittedDocs = $docs->whereNotNull('actual_date');
                    $totalSubmitted = $submittedDocs->count();

                    if ($totalSubmitted > 0) {
                        // Hitung yang On-Time (actual <= due)
                        $onTimeCount = $submittedDocs->filter(function ($doc) {
                            return $doc->actual_date <= $doc->due_date;
                        })->count();

                        // Rumus Persentase
                        $percentage = round(($onTimeCount / $totalSubmitted) * 100, 1);
                    } else {
                        // Jika belum ada yang submit sama sekali di stage ini, anggap 0% atau skip?
                        // Kita set 0 saja
                        $percentage = 0;
                    }

                    $chartLabels[] = $stageName; // Sumbu X (Step 1, Step 2...)
                    $chartValues[] = $percentage; // Sumbu Y (100, 80...)
                }

                // --- LOGIKA MENGAMBIL DATA DELAY ---
                // Ambil semua dokumen di project ini yang statusnya Delay
                $delayDocuments = $selectedProject->documents->filter(function ($doc) {
                    return $doc->status_text === 'Delay';
                });
            }
        }

        return view('kpi.index', compact(
            'customers',
            'selectedProject',
            'chartLabels',
            'chartValues',
            'delayDocuments'
        ));
    }

    public function getModels(Request $request)
    {
        // Cari model berdasarkan customer code
        $models = DB::table('projects')
            ->where('customer_code', $request->customer_code)
            ->select('model')
            ->distinct()
            ->orderBy('model')
            ->get();

        return response()->json($models);
    }

    public function getParts(Request $request)
    {
        // Cari part number berdasarkan customer dan model
        $parts = DB::table('projects')
            ->where('customer_code', $request->customer_code)
            ->where('model', $request->model)
            ->select('part_number', 'part_name') // Ambil part_name juga biar informatif
            ->distinct()
            ->orderBy('part_number')
            ->get();

        return response()->json($parts);
    }

    public function getVariants(Request $request)
    {
        // Cari kombinasi Suffix dan Minor Change berdasarkan part number
        // Kita perlu customer dan model juga agar filternya presisi
        $variants = DB::table('projects')
            ->where('customer_code', $request->customer_code)
            ->where('model', $request->model)
            ->where('part_number', $request->part_number)
            ->select('suffix', 'minor_change')
            ->orderBy('suffix')
            ->get();

        return response()->json($variants);
    }
}
