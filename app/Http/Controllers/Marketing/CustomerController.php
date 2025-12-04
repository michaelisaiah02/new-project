<?php

namespace App\Http\Controllers\Marketing;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with('department')->orderBy('name')->get();
        $departments = Department::where('name', 'LIKE', '%Engineering%')->get();

        return view('marketing.customers.index', compact('customers', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'unique:customers,code'],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id']
        ]);

        Customer::create($validated);

        return redirect()->route('marketing.customers.index')->with('success', 'Customer added successfully.');
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
