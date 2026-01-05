<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SikadCredential;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'login_as' => 'required|in:student,validator,admin',
        ]);

        // Login sebagai Mahasiswa
        if ($request->login_as === 'student') {
            $cred = SikadCredential::where('student_id', $request->username)->first();
            if ($cred && Hash::check($request->password, $cred->password_hash)) {
                session(['auth_role' => 'student', 'student_id' => $cred->student_id]);
                return redirect()->intended(route('student.dashboard'));
            }
            return back()->withErrors(['username' => 'NIM atau password salah.'])->withInput();
        }

        // Login sebagai Admin - redirect ke Filament login
        if ($request->login_as === 'admin') {
            if (Auth::attempt(['email' => $request->username, 'password' => $request->password])) {
                $user = Auth::user();
                if ($user->role === 'Admin') {
                    return redirect('/admin');
                }
                Auth::logout();
            }
            return back()->withErrors(['username' => 'Email atau password salah, atau Anda bukan Admin.'])->withInput();
        }

        // Login sebagai Validator
        if ($request->login_as === 'validator') {
            if (Auth::attempt(['email' => $request->username, 'password' => $request->password])) {
                $user = Auth::user();
                if ($user->role === 'Validator') {
                    return redirect()->intended(route('validator.dashboard'));
                }
                Auth::logout();
            }
            return back()->withErrors(['username' => 'Email atau password salah, atau Anda bukan Validator.'])->withInput();
        }

        return back()->withErrors(['username' => 'Login gagal.'])->withInput();
    }

    public function logout(Request $request)
    {
        if (session('auth_role') === 'student') {
            $request->session()->forget(['auth_role', 'student_id']);
        } else {
            Auth::logout();
        }
        return redirect('/');
    }
}