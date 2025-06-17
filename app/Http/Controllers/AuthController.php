<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $pengguna = Pengguna::where('email', $request->email)->first();

        if ($pengguna && Hash::check($request->password, $pengguna->password)) {
            if ($pengguna->role) {
                Auth::login($pengguna);

                // Update last login
                $pengguna->update(['last_login' => now()]);

                // Redirect based on role
                switch ($pengguna->role) {
                    case 'admin':
                        return redirect()->route('admin.dashboard');
                    case 'kepalalab':
                        return redirect()->route('kepalalab.dashboard');
                    case 'asistenlab':
                        return redirect()->route('asistenlab.dashboard');
                }
            } else {
                return back()->withErrors(['email' => 'Akun anda belum divalidasi, harap menunggu manajemen role dari admin.']);
            }
        }

        return back()->withErrors(['email' => 'Email atau password salah!']);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama_pengguna' => 'required|string|max:100',
            'email' => 'required|email|unique:pengguna,email',
            'password' => 'required|min:6|confirmed',
        ]);

        Pengguna::create([
            'nama_pengguna' => $request->nama_pengguna,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => null,
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil, harap menunggu manajemen role dari admin.');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
