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
        // --- A. PERSIAPAN DATA CUSTOMER (Dropdown) ---
        // Kita filter list customer sesuai hak akses user juga
        $user = auth()->user();
        $deptName = strtolower($user->department->name ?? '');
        $hasGlobalAccess = str_contains($deptName, 'management') || str_contains($deptName, 'marketing');

        $custQuery = Customer::orderBy('name');

        if (! $hasGlobalAccess) {
            // Engineering X cuma bisa lihat customer milik Engineering X
            $custQuery->where('department_id', $user->department_id);
        }
        $customers = $custQuery->get();

        // --- B. CEK FILTER DARI USER ---
        $filters = $request->only(['customer', 'model', 'part_number', 'suffix', 'minor_change']);
        $hasFilter = collect($filters)->filter()->isNotEmpty();

        if (! $hasFilter) {
            return view('masspro.index', [
                'customers' => $customers, // Kirim list customer yang sudah difilter
                'massproRecords' => collect(),
                'autoFilled' => [],
            ]);
        }

        // --- C. QUERY DATA PROJECT ---
        $query = Project::query()
            ->whereIn('remark', ['completed', 'canceled']);

        // >>> TERAPKAN FILTER DEPARTMENT DISINI <<<
        $this->applyDepartmentFilter($query);

        // Filter Input User
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
        if ($request->filled('remark') && $request->remark != 'all') {
            $query->where('remark', $request->remark);
        }

        $massproRecords = $query->orderBy('updated_at', 'desc')->get();

        // --- D. AUTO FILL LOGIC ---
        $autoFilled = [];
        if ($request->filled('part_number') && (! $request->filled('customer') || ! $request->filled('model'))) {
            $parent = $massproRecords->first();
            if ($parent) {
                if (! $request->filled('customer')) {
                    $autoFilled['customer'] = $parent->customer_code;
                }
                if (! $request->filled('model')) {
                    $autoFilled['model'] = $parent->model;
                }
            }
        }

        return view('masspro.index', compact('customers', 'massproRecords', 'autoFilled'));
    }

    private function applyDepartmentFilter($query)
    {
        $user = auth()->user();
        $deptName = strtolower($user->department->name ?? '');

        // 1. Definisikan siapa yang BOLEH akses semua (Global Access)
        // Sesuaikan stringnya dengan database kamu (case-insensitive)
        $hasGlobalAccess = str_contains($deptName, 'management') || str_contains($deptName, 'marketing');

        if (! $hasGlobalAccess) {
            // 2. Jika Engineering (atau lainnya), filter berdasarkan relasi Customer -> Department
            $query->whereHas('customer', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        return $query;
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

    // =========================================================================
    // API METHODS
    // =========================================================================

    public function getModels(Request $request)
    {
        $q = Project::select('model')->distinct()->whereIn('remark', ['completed', 'canceled']);

        $this->applyDepartmentFilter($q); // << Pasang Filter

        if ($request->filled('customer_code')) {
            $q->where('customer_code', $request->customer_code);
        }
        if ($request->filled('part_number')) {
            $q->where('part_number', $request->part_number);
        }
        if ($request->filled('suffix')) {
            $q->where('suffix', $request->suffix);
        }
        if ($request->filled('minor_change')) {
            $q->where('minor_change', $request->minor_change);
        }
        if ($request->filled('remark') && $request->remark != 'all') {
            $q->where('remark', $request->remark);
        }

        return response()->json($q->orderBy('model')->pluck('model'));
    }

    public function getParts(Request $request)
    {
        $q = Project::select('part_number', 'part_name')->distinct()->whereIn('remark', ['completed', 'canceled']);

        $this->applyDepartmentFilter($q); // << Pasang Filter

        if ($request->filled('customer_code')) {
            $q->where('customer_code', $request->customer_code);
        }
        if ($request->filled('model')) {
            $q->where('model', $request->model);
        }
        if ($request->filled('suffix')) {
            $q->where('suffix', $request->suffix);
        }
        if ($request->filled('minor_change')) {
            $q->where('minor_change', $request->minor_change);
        }
        if ($request->filled('remark') && $request->remark != 'all') {
            $q->where('remark', $request->remark);
        }

        return response()->json($q->orderBy('part_number')->get());
    }

    public function getSuffixes(Request $request)
    {
        $q = Project::select('suffix')->distinct()->whereNotNull('suffix')->where('suffix', '!=', '')->whereIn('remark', ['completed', 'canceled']);

        $this->applyDepartmentFilter($q); // << Pasang Filter

        if ($request->filled('customer')) {
            $q->where('customer_code', $request->customer);
        }
        if ($request->filled('model')) {
            $q->where('model', $request->model);
        }
        if ($request->filled('part_number')) {
            $q->where('part_number', $request->part_number);
        }
        if ($request->filled('minor_change')) {
            $q->where('minor_change', $request->minor_change);
        }
        if ($request->filled('remark') && $request->remark != 'all') {
            $q->where('remark', $request->remark);
        }

        return response()->json($q->orderBy('suffix')->pluck('suffix'));
    }

    public function getMinorChanges(Request $request)
    {
        $q = Project::select('minor_change')->distinct()->whereNotNull('minor_change')->where('minor_change', '!=', '')->whereIn('remark', ['completed', 'canceled']);

        $this->applyDepartmentFilter($q); // << Pasang Filter

        if ($request->filled('customer')) {
            $q->where('customer_code', $request->customer);
        }
        if ($request->filled('model')) {
            $q->where('model', $request->model);
        }
        if ($request->filled('part_number')) {
            $q->where('part_number', $request->part_number);
        }
        if ($request->filled('suffix')) {
            $q->where('suffix', $request->suffix);
        }
        if ($request->filled('remark') && $request->remark != 'all') {
            $q->where('remark', $request->remark);
        }

        return response()->json($q->orderBy('minor_change')->pluck('minor_change'));
    }

    public function getRemarks(Request $request)
    {
        // 1. Base Query: Ambil distinct remark
        $q = Project::select('remark')
            ->distinct()
            ->whereIn('remark', ['completed', 'canceled']); // Tetap dibatasi scope Masspro

        // 2. Security Filter
        $this->applyDepartmentFilter($q);

        // 3. Cross-Filtering (Dengar input lain)
        // Remark harus peka terhadap perubahan Customer, Model, Part, Suffix, MC
        if ($request->filled('customer')) {
            $q->where('customer_code', $request->customer);
        }
        if ($request->filled('model')) {
            $q->where('model', $request->model);
        }
        if ($request->filled('part_number')) {
            $q->where('part_number', $request->part_number);
        }
        if ($request->filled('suffix')) {
            $q->where('suffix', $request->suffix);
        }
        if ($request->filled('minor_change')) {
            $q->where('minor_change', $request->minor_change);
        }

        // Return array string
        return response()->json($q->orderBy('remark')->pluck('remark'));
    }
}
