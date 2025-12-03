<?php

namespace App\Http\Controllers\Marketing;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('department')->get();
        $departments = Department::all();

        return view('marketing.users.index', compact('users', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'id' => ['required', 'size:5', 'unique:users,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['required', 'string', 'min:6'],
            'approved' => ['boolean'],
            'checked' => ['boolean'],
        ]);

        // if ($validated['role'] !== 'admin' && $validated['approved']) {
        //     return back()->withErrors(['approved' => 'Approved can only be true if the role is admin.']);
        // }

        // if ($validated['role'] === 'guest' && $validated['checked']) {
        //     return back()->withErrors(['checked' => 'Checked can only be true if the role is not guest.']);
        // }

        // approved dan checked dari semua user hanya satu yang boleh true, jadi jika ada yang true, maka yang lain otomatis di ubah jadi false
        // if ($validated['approved']) {
        //     User::where('approved', true)->update(['approved' => false]);
        // }
        // if ($validated['checked']) {
        //     User::where('checked', true)->update(['checked' => false]);
        // }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('marketing.users.index')->with('success', 'User added successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'id' => ['required', 'size:5', Rule::unique('users', 'id')->ignore($user->id)],
            'department_id' => 'required|exists:departments,id',
            'password' => 'nullable|string|min:6',
            // no need to validate approved/checked here
        ]);

        // read them as booleans
        $approved = $request->boolean('approved');
        $checked = $request->boolean('checked');

        // role-based rules
        // if ($validated['role'] !== 'admin' && $approved) {
        //     return back()->withErrors(['approved' => 'Only admin can be approved']);
        // }
        // if ($validated['role'] === 'guest' && $checked) {
        //     return back()->withErrors(['checked' => 'Guest cannot be checked']);
        // }

        // enforce uniqueness: only one approved & one checked
        // if ($approved) {
        //     User::where('approved', true)->update(['approved' => false]);
        // }
        // if ($checked) {
        //     User::where('checked', true)->update(['checked' => false]);
        // }

        // Jika tidak ada user lain yang approved dan user ini tidak ingin di-approve, tolak
        // if (!User::where('approved', true)->where('id', '!=', $user->id)->exists() && $approved === false) {
        //     return back()->withErrors(['approved' => 'At least one user must be approved.']);
        // }

        // Jika tidak ada user lain yang checked dan user ini tidak ingin di-check, tolak
        // if (!User::where('checked', true)->where('id', '!=', $user->id)->exists() && $checked === false) {
        //     return back()->withErrors(['checked' => 'At least one user must be checked.']);
        // }

        // put everything into $data for update
        $data = $validated;
        $data['approved'] = $approved;
        $data['checked'] = $checked;

        // hash password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // if password is null, remove it from $data to avoid updating it
        if (is_null($request->password)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('marketing.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('marketing.users.index')->with('success', 'User has been successfully deleted.');
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = User::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('id', 'like', "%{$keyword}%")
                        ->orWhereHas('department', function ($deptQuery) use ($keyword) {
                            $deptQuery->where('name', 'like', "%{$keyword}%");
                        });
                });
            });

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'html' => view('marketing.users.partials.table_rows', compact('users'))->render(),
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

        $superiors = User::whereIn('role', $rolesToFetch)->where('department_id', '=', $departmentId)->get();

        return response()->json($superiors);
    }
}
