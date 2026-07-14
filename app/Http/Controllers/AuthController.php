<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $authData = [
            $loginType => $credentials['login'],
            'password' => $credentials['password'],
        ];

        if (Auth::attempt($authData, $request->has('remember'))) {
            $user = Auth::user();
            $request->session()->regenerate();
            
            ActivityLog::log('Login', "Pengguna {$user->name} ({$user->role}) berhasil login.", $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil!',
                'redirect' => $this->getRedirectPath($user),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username/Email atau password salah.',
        ], 401);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            ActivityLog::log('Logout', "Pengguna {$user->name} berhasil logout.", $user->id);
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect logic helper
     */
    private function redirectBasedOnRole($user)
    {
        return redirect()->to($this->getRedirectPath($user));
    }

    /**
     * Helper to get dashboard path by role
     */
    private function getRedirectPath($user): string
    {
        switch ($user->role) {
            case 'admin':
                return '/admin';
            case 'dosen':
                return '/dosen';
            case 'mahasiswa':
                return '/mahasiswa';
            default:
                return '/';
        }
    }
}
