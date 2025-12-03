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
        $customers = Customer::with('department')->get();
        $departments = Department::all();

        return view('marketing.customers.index', compact('customers', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'unique:customers,code'],
            'name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'exists:departments,id']
        ]);

        Customer::create($validated);

        return redirect()->route('marketing.customers.index')->with('success', 'Customer added successfully.');
    }

    public function update(Request $request, $code)
    {
        $customer = Customer::findOrFail($code);

        $validated = $request->validate([
            'code' => ['required', Rule::unique('customers', 'code')->ignore($customer->code)],
            'name' => 'required|string|max:255',
            'department' => 'required|exists:departments,id',
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

        $customers = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'html' => view('marketing.customers.partials.table_rows', compact('customers'))->render(),
        ]);
    }

    public function getSuperiors(Request $request)
    {
        $role = $request->query('role');
        $departmentId = $request->query('department_id');

        switch ($role) {
            case 'leader':
                $rolesToFetch = ['supervisor'];
                break;
            case 'supervisor':
                $rolesToFetch = ['ypq'];
                break;
            case 'ypq':
                $rolesToFetch = ['management'];
                break;
            default:
                return response()->json([]);
        }

        $superiors = Customer::whereIn('role', $rolesToFetch)->where('department_id', '=', $departmentId)->get();

        return response()->json($superiors);
    }
}
