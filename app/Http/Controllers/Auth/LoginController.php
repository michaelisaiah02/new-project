<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ControlLeader\ChecksheetDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Menampilkan form login berdasarkan aplikasi yang dipilih.
     */
    public function showLoginForm(Request $request)
    {
        return view('auth.login');
    }

    /**
     * Memproses upaya login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'id' => 'required|string|size:5',
            'password' => 'required|string'
        ]);
        $credentials = $request->only('id', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect sesuai dengan departemen pengguna
            switch (Auth::user()->department->name) {
                case str_starts_with(Auth::user()->department->name, 'Marketing') ? Auth::user()->department->name : null:
                    return redirect()->intended('/marketing');
                case str_starts_with(Auth::user()->department->name, 'Engineering') ? Auth::user()->department->name : null:
                    return redirect()->intended('/engineering');
                case str_starts_with(Auth::user()->department->name, 'Management') ? Auth::user()->department->name : null:
                    return redirect()->intended('/management');
                default:
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    throw ValidationException::withMessages([
                        'error' => 'Departemen tidak dikenali untuk aplikasi ini.',
                    ]);
            }
        }

        throw ValidationException::withMessages([
            'error' => 'Employee ID atau Password salah untuk aplikasi ini.',
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
