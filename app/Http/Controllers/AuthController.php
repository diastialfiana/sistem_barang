<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
  
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nip' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            // Timestamp updates handled by UpdateLastLogin listener

            // Redirect to dashboard or intended page
            return redirect()->intended('dashboard');
        }

        throw ValidationException::withMessages([
            'nip' => 'NIP atau password salah.',
        ]);
    }
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function resetPasswordToNip(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|exists:users,nip',
        ]);

        $user = \App\Models\User::where('nip', $request->nip)->firstOrFail();

        // Reset password to NIP
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($user->nip)
        ]);

        return redirect()->route('login')->with('success', 'Password berhasil direset ke NIP: ' . $user->nip . '. Silakan login.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
