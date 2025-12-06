<?php

namespace App\Http\Controllers\Marketing;

use App\Models\Customer;
use App\Models\Department;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Models\CustomerStage;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

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
            'department_id' => ['required', 'exists:departments,id']
        ]);

        Customer::create($validated);

        return redirect()
            ->route('marketing.customers.createStage', $validated['code'])
            ->with('success', 'Customer added successfully. Now define document stages.');
    }

    public function createStage(Customer $customer)
    {
        $lastStage = $customer->stages()->max('stage_number') ?? 0;
        $nextStageNumber = $lastStage + 1;
        $stageNumber = $nextStageNumber;

        $usedDocs = $customer->stages()
            ->with('documents:id')   // load docs
            ->get()
            ->pluck('documents')
            ->flatten()
            ->pluck('id');

        $availableDocuments = DocumentType::whereNotIn('id', $usedDocs)->get();

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
            'stage_name'   => 'string|nullable',
            'document_type_ids' => 'required|array',
            'qr_position' => 'required|array',
        ]);

        $stage = CustomerStage::create([
            'stage_number' => $validated['stage_number'],
            'stage_name'   => $validated['stage_name'],
            'customer_code' => $customer->code,
            'document_type_id' => null, // gak dipakai lagi
        ]);

        foreach ($validated['document_type_ids'] as $docId) {
            $stage->documents()->attach($docId, [
                'qr_position' => $validated['qr_position'][$docId]
            ]);
        }

        if ($request->decision === 'finish') {
            return redirect()->route('marketing.customers.index')
                ->with('success', 'Customer stages completed!');
        }

        return redirect()->route('marketing.customers.createStage', $customer->code)
            ->with('success', 'Stage added! Next stage ready.');
    }


    public function update(Request $request, $code)
    {
        $customer = Customer::findOrFail($code);

        $validated = $request->validate([
            'code' => ['required', Rule::unique('customers', 'code')->ignore($customer->code, 'code')],
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            // no need to validate approved/checked here
        ]);

        $data = $validated;

        $customer->update($data);

        return redirect()->route('marketing.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy($code)
    {
        $customer = Customer::findOrFail($code);
        $customer->delete();

        return redirect()->route('marketing.customers.index')->with('success', 'Customer has been successfully deleted.');
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
            'html' => view('marketing.customers.partials.table_rows', compact('customers'))->render(),
        ]);
    }
}
