<?php

namespace App\Http\Controllers\Marketing;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Helpers\CountryHelper;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

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
            'id' => ['required', 'size:5', 'unique:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'whatsapp' => ['required', 'string', 'max:13'],  // validasi custom nanti
            'password' => ['required', 'string', 'min:6'],
            'approved' => ['boolean'],
            'checked' => ['boolean'],
        ]);

        // Clean number → remove non-digits
        $number = preg_replace('/\D/', '', $validated['whatsapp']);

        // Remove leading 0 (089 → 89)
        $validated['whatsapp'] = ltrim($number, '0');

        // kalau sudah 62 di depannya, hapus 62
        if (str_starts_with($validated['whatsapp'], '62')) {
            $validated['whatsapp'] = substr($validated['whatsapp'], 2);
        }

        // Password hash
        $validated['password'] = Hash::make($validated['password']);

        $validated['approved'] = $request->boolean('approved');
        $validated['checked'] = $request->boolean('checked');

        $dept = Department::find($validated['department_id']);
        $deptType = $dept->type();

        if ($deptType === 'engineering' && $validated['checked'] && $validated['approved']) {
            return back()->withErrors(['checked' => 'Checked & Approved tidak boleh aktif bersamaan']);
        }

        // apply department rules
        switch ($deptType) {
            case 'marketing':
                $validated['approved'] = false;
                $validated['checked'] = false;
                break;

            case 'management':
                $validated['approved'] = true;
                $validated['checked'] = false;
                break;

            case 'engineering':
                if ($validated['approved']) {
                    User::where('department_id', $validated['department_id'])
                        ->update(['approved' => false]);
                }

                if ($validated['approved'] && $validated['checked']) {
                    $validated['checked'] = false;
                }
                break;
        }

        User::create($validated);

        return redirect()->route('marketing.users.index')->with('success', 'User added successfully.');
    }
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'id' => ['required', 'size:5', Rule::unique('users', 'id')->ignore($user->id)],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'whatsapp' => ['required', 'string'],  // validasi custom nanti
            'password' => ['nullable', 'string', 'min:6'],
            'approved' => ['boolean'],
            'checked' => ['boolean'],
        ]);

        // Clean number → remove non-digits
        $number = preg_replace('/\D/', '', $validated['whatsapp']);

        // Remove leading 0 (089 → 89)
        $validated['whatsapp'] = ltrim($number, '0');

        // kalau sudah 62 di depannya, hapus 62
        if (str_starts_with($validated['whatsapp'], '62')) {
            $validated['whatsapp'] = substr($validated['whatsapp'], 2);
        }

        // read them as booleans
        $approved = $request->boolean('approved');
        $checked = $request->boolean('checked');

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

        $dept = Department::find($data['department_id']);
        $deptType = $dept->type();

        if ($deptType === 'engineering' && $data['checked'] && $data['approved']) {
            return back()->withErrors(['checked' => 'Checked & Approved tidak boleh aktif bersamaan']);
        }

        switch ($deptType) {
            case 'marketing':
                $data['approved'] = false;
                $data['checked'] = false;
                break;

            case 'management':
                $data['approved'] = true;
                $data['checked'] = false;
                break;

            case 'engineering':
                if (!empty($data['approved']))
                    User::where('department_id', $data['department_id'])
                        ->where('id', '!=', $user->id)
                        ->update(['approved' => false]);

                if (!empty($data['approved']) && !empty($data['checked'])) {
                    $data['checked'] = false;
                }
                break;
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
