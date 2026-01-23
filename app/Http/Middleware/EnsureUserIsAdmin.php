<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login')->withErrors(['login' => 'Anda harus login terlebih dahulu.']);
        }

        if (auth()->user()->role !== 'Admin') {
            return redirect()->route('login')->withErrors(['login' => 'Akses ditolak. Anda bukan Admin.']);
        }

        return $next($request);
    }
}
