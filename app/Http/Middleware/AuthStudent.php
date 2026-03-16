<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthStudent
{
    public function handle(Request $request, Closure $next)
    {
        if (session('auth_role') !== 'student' || ! session('student_id')) {
            return redirect()->route('login')->withErrors(['login' => 'Anda harus login sebagai mahasiswa.']);
        }

        return $next($request);
    }
}
