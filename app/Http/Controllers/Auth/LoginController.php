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
            'employeeID' => 'required|string',
            'password' => 'required|string',
            'app' => 'required|string|in:kalibrasi,control_leader',
            'shift' => 'required_if:app,control_leader|in:1,2,3',
        ]);

        $credentials = $request->only('employeeID', 'password');
        $activeApp = $request->input('app');

        // Pilih guard mana yang akan digunakan untuk otentikasi
        $guard = $activeApp === 'control_leader'
            ? Auth::guard('web_control_leader')
            : Auth::guard('web');

        if ($guard->attempt($credentials)) {
            $request->session()->regenerate();
            $request->session()->put('active_app', $activeApp);
            $request->session()->forget(['login_app_type', 'url.intended']);

            // >>> Tambahan: single-device hanya untuk control_leader
            if ($activeApp === 'control_leader') {
                $user = $guard->user();
                if (!$user->can_login) {
                    $guard->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    throw ValidationException::withMessages(['error' => 'Akun ini tidak diizinkan login.']);
                }
                $sid = $request->session()->getId();
                $request->session()->put('shift', $request->input('shift'));

                // LOCK: hanya block takeover kalau user masih mengisi (ping < 3 menit)
                $LOCK_TTL_MIN = 3;
                $lockActive = $user->cl_in_progress
                    && $user->cl_last_ping
                    && now()->diffInMinutes($user->cl_last_ping) < $LOCK_TTL_MIN;

                if (!empty($user->control_session_id) && $user->control_session_id !== $sid && $lockActive) {
                    $guard->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => 'Akun CONTROL LEADER sedang mengisi checksheet di perangkat lain.',
                    ]);
                }

                // takeover OK (kalau tidak sedang aktif)
                $user->forceFill([
                    'control_session_id' => $sid,
                    'cl_in_progress' => false,     // reset flag nyangkut
                    'cl_last_ping' => now(),
                ])->save();

                $draft = ChecksheetDraft::where('user_id', $user->id)
                    ->where('is_active', true)    // ← tanpa TTL
                    ->latest('updated_at')
                    ->first();

                if ($draft) {
                    // hapus draft yg masih aktif (karena sudah logout > 3 menit)
                    $draft->delete();

                    return redirect()->route('control.checksheets.create', [
                        'detail' => $draft->schedule_detail_id,
                        'type' => $draft->phase,
                    ]);
                }

                // kalau tidak sedang mengisi (atau lock kadaluarsa) → boleh takeover sesi lama
                $oldSid = $user->control_session_id;

                // (opsional) jika SESSION_DRIVER=database, hapus baris sesi lama
                if (config('session.driver') === 'database' && $oldSid && $oldSid !== $sid) {
                    \DB::connection(config('session.connection'))
                        ->table(config('session.table', 'sessions'))
                        ->where('id', $oldSid)->delete();
                }
            }

            return redirect()->intended('/dashboard');
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
        $request->session()->forget(['active_app', 'login_app_type', 'url.intended', 'cl_in_progress', 'shift']);

        if (Auth::guard('web_control_leader')->check()) {
            Auth::guard('web_control_leader')->user()
                ?->forceFill(['control_session_id' => null, 'cl_in_progress' => false])
                ->save();
        }

        Auth::logout();
        Auth::guard('web')->logout();
        Auth::guard('web_control_leader')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Arahkan ke halaman login netral
        return redirect('/');
    }
}
