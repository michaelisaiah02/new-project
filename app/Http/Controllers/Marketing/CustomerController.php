<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerStage;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with('department')->orderBy('name')->get();
        $departments = Department::where('name', 'LIKE', '%Engineering%')->get();

        return view('marketing.customers.index', compact('customers', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('name', 'LIKE', '%Engineering%')->get();

        return view('marketing.customers.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'unique:customers,code'],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        Customer::create($validated);

        return redirect()
            ->route('marketing.customers.createStage', $validated['code'])
            ->with('success', 'Customer added successfully. Now define document stages.');
    }

    public function edit(Customer $customer)
    {
        $departments = Department::where('name', 'LIKE', '%Engineering%')->get();

        return view('marketing.customers.edit', compact('customer', 'departments'));
    }

    public function update(Request $request, string $code)
    {
        $customer = Customer::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'code' => ['required', Rule::unique('customers', 'code')->ignore($customer->code, 'code')],
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        $customer->update($validated);

        // kalau nama customer berubah, update juga di semua project terkait
        if ($customer->wasChanged('name')) {
            Project::where('customer_code', $customer->code)
                ->update(['customer_name_snapshot' => $customer->name]);
        }

        return redirect()->route('marketing.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy($code)
    {
        $customer = Customer::findOrFail($code);
        DB::transaction(function () use ($customer) {
            // Hapus project yang BELUM completed
            Project::where('customer_code', $customer->code)
                ->where('remark', '!=', 'completed')
                ->delete();

            // Project completed → customer_code jadi null
            Project::where('customer_code', $customer->code)
                ->where('remark', 'completed')
                ->update(['customer_code' => null]);

            // Baru hapus customer
            $customer->delete();
        });

        return redirect()->route('marketing.customers.index')->with('success', 'Customer has been successfully deleted.');
    }

    public function createStage(Customer $customer)
    {
        $lastStage = $customer->stages()->max('stage_number') ?? 0;
        $nextStageNumber = $lastStage + 1;
        $stageNumber = $nextStageNumber;

        $usedDocs = $customer->stages()
            ->with('documents:code')   // load docs
            ->get()
            ->pluck('documents')
            ->flatten()
            ->pluck('code');

        $availableDocuments = DocumentType::whereNotIn('code', $usedDocs)->get();

        return view('marketing.customers.create-stages', compact(
            'customer',
            'stageNumber',
            'availableDocuments'
        ));
    }

    public function storeStage(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'stage_number' => 'required|integer',
            'stage_name' => 'string|nullable',
            'document_type_codes' => 'required|array',
            'qr_position' => 'required|array',
        ]);

        $stage = CustomerStage::create([
            'stage_number' => $validated['stage_number'],
            'stage_name' => $validated['stage_name'],
            'customer_code' => $customer->code,
            'document_type_code' => null,
        ]);

        foreach ($validated['document_type_codes'] as $docId) {
            $stage->documents()->attach($docId, [
                'qr_position' => $validated['qr_position'][$docId],
            ]);
        }

        if ($request->decision === 'finish') {
            return redirect()->route('marketing.customers.index')
                ->with('success', 'Customer stages completed!');
        }

        return redirect()->route('marketing.customers.createStage', $customer->code)
            ->with('success', 'Stage added! Next stage ready.');
    }

    public function editStage(Customer $customer, $stageNumber)
    {
        // cek kalau stage belum ada → create dummy instance
        $stage = $customer->stages()
            ->where('stage_number', $stageNumber)
            ->first();

        // kalau belum ada stage ini → buat model dummy
        if (! $stage) {
            $stage = new CustomerStage([
                'customer_code' => $customer->code,
                'stage_number' => $stageNumber,
                'stage_name' => null,
            ]);
        }

        // documents used in other stages
        $usedDocs = DB::table('customer_stage_documents')
            ->join('customer_stages', 'customer_stage_documents.customer_stage_id', '=', 'customer_stages.id')
            ->where('customer_stages.customer_code', $customer->code)
            ->where('customer_stages.stage_number', '!=', $stageNumber)
            ->pluck('document_type_code');

        // current stage docs
        $currentDocs = $stage->exists
            ? $stage->documents()->pluck('document_type_code')
            : collect();

        $availableDocuments = DocumentType::whereNotIn('code', $usedDocs);

        if ($currentDocs->isNotEmpty()) {
            $inList = $currentDocs
                ->map(fn ($c) => "'".addslashes($c)."'")
                ->implode(',');

            $availableDocuments->orderByRaw("
        CASE
            WHEN code IN ($inList) THEN 0
            ELSE 1
        END
    ");
        }

        // tetap rapihin by name
        $availableDocuments = $availableDocuments
            ->orderBy('name')
            ->get();

        $maxStage = $customer->stages()->max('stage_number') ?? 0;

        $usedDocs = DB::table('customer_stage_documents')
            ->join('customer_stages', 'customer_stage_documents.customer_stage_id', '=', 'customer_stages.id')
            ->where('customer_stages.customer_code', $customer->code)
            ->pluck('document_type_code');

        $remainingDocs = DocumentType::whereNotIn('code', $usedDocs)->count();

        $canAddStage = $remainingDocs > 0;

        return view('marketing.customers.edit-stages', compact(
            'customer',
            'stageNumber',
            'stage',
            'availableDocuments',
            'currentDocs',
            'maxStage',
            'canAddStage'
        ));
    }

    public function saveStage(Request $request, Customer $customer, int $stageNumber)
    {
        $stage = CustomerStage::where('customer_code', $customer->code)
            ->where('stage_number', $stageNumber)
            ->first();
        $docIds = $request->input('document_type_codes', []);

        // 1️⃣ Cegah stage baru tanpa dokumen
        if (! $stage && count($docIds) === 0) {
            return back()
                ->withErrors(['document_type_codes' => 'Minimal pilih 1 dokumen untuk stage baru.'])
                ->withInput();
        }

        // 2️⃣ (opsional) Kalau mau juga cegah edit stage jadi kosong:
        if ($stage && count($docIds) === 0) {
            return back()
                ->withErrors(['document_type_codes' => 'Stage tidak boleh kosong dokumen. Gunakan fitur delete stage kalau mau menghapus.'])
                ->withInput();
        }

        $validated = $request->validate([
            'stage_name' => 'nullable|string',
            'document_type_codes' => 'nullable|array',
            'qr_position' => 'nullable|array',
        ]);

        // --- SAVE / UPDATE STAGE ---
        $stage = CustomerStage::firstOrCreate(
            ['customer_code' => $customer->code, 'stage_number' => $stageNumber],
            ['stage_name' => $validated['stage_name']]
        );

        $stage->update(['stage_name' => $validated['stage_name']]);

        // pastikan setiap dokumen yang dipilih punya qr_position
        $errors = [];

        $syncData = [];
        foreach ($validated['document_type_codes'] ?? [] as $docCode) {
            if (empty($validated['qr_position'][$docCode] ?? null)) {
                $errors["qr_position.$docCode"] = 'Dokumen yang dipilih harus punya posisi QR.';
            }
            $syncData[$docCode] = [
                'qr_position' => $validated['qr_position'][$docCode] ?? null,
            ];
        }
        if (! empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }
        $stage->documents()->sync($syncData);

        // --- HANDLE ACTION ---
        $action = $request->input('form_action');

        if ($action === 'save') {
            return back()->with('success', 'Stage saved.');
        }

        // dd($action);
        if ($action === 'navigate') {
            $target = (int) $request->input('target_stage', $stageNumber);

            return redirect()->route('marketing.customers.editStage', [
                'customer' => $customer->code,
                'stageNumber' => $target,
            ]);
        }

        if ($action === 'add_stage') {
            // hitung sisa dokumen
            $usedDocs = DB::table('customer_stage_documents')
                ->join('customer_stages', 'customer_stage_documents.customer_stage_id', '=', 'customer_stages.id')
                ->where('customer_stages.customer_code', $customer->code)
                ->pluck('document_type_code');
            $remainingDocs = DocumentType::whereNotIn('code', $usedDocs)->count();

            if ($remainingDocs === 0) {
                return back()->with('error', 'Semua dokumen sudah dipakai, tidak bisa tambah stage baru.');
            }

            $maxStage = $customer->stages()->max('stage_number') ?? 0;
            $newStageNumber = $maxStage + 1;

            return redirect()->route('marketing.customers.editStage', [
                'customer' => $customer->code,
                'stageNumber' => $newStageNumber,
            ]);
        }

        if ($action === 'finish') {
            $selectedDocs = $request->input('document_type_codes', []);

            // 1. Cek apakah DM dipilih di form saat ini
            $dmSelectedInCurrentForm = in_array('DM', $selectedDocs);

            // 2. Cek apakah DM SUDAH ADA di database (di stage manapun untuk customer ini)
            // Kita pakai query DB agar akurat mengecek semua stage milik customer ini
            $dmAlreadyExistsInDb = DB::table('customer_stage_documents')
                ->join('customer_stages', 'customer_stage_documents.customer_stage_id', '=', 'customer_stages.id')
                ->where('customer_stages.customer_code', $customer->code)
                ->where('customer_stage_documents.document_type_code', 'DM')
                ->exists();

            // LOGIC: Auto-add DM hanya jika:
            // TIDAK dipilih di form saat ini DAN TIDAK ada di database sebelumnya
            if (! $dmSelectedInCurrentForm && ! $dmAlreadyExistsInDb) {

                $lastStage = CustomerStage::where('customer_code', $customer->code)
                    ->orderBy('stage_number', 'desc')
                    ->first();

                if ($lastStage) {
                    $lastStage->documents()->attach('DM', [
                        'qr_position' => 'bottom_right',
                    ]);
                }
            }

            return redirect()->route('marketing.customers.index')
                ->with('success', 'Customer stages updated.');
        }

        // fallback
        return back();
    }

    public function destroyStage(Customer $customer, int $stageNumber)
    {
        $stage = $customer->stages()
            ->where('stage_number', $stageNumber)
            ->firstOrFail();

        $stage->delete();
        $this->reorderStages($customer);

        return redirect()->route('marketing.customers.editStage', [
            'customer' => $customer->code,
            'stageNumber' => max(1, $stageNumber - 1),
        ])->with('success', 'Stage deleted successfully.');
    }

    protected function reorderStages(Customer $customer)
    {
        CustomerStage::where('customer_code', $customer->code)
            ->orderBy('stage_number')
            ->get()
            ->each(function ($stage, $index) {
                $stage->update(['stage_number' => $index + 1]);
            });
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = Customer::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('code', 'like', "%{$keyword}%")
                        ->orWhereHas('department', function ($deptQuery) use ($keyword) {
                            $deptQuery->where('name', 'like', "%{$keyword}%");
                        });
                });
            });

        $customers = $query->orderBy('name')->get();

        return response()->json([
            'html' => view('marketing.customers.partials.table-rows', compact('customers'))->render(),
        ]);
    }
}
