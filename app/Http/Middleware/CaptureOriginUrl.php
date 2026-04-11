<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureOriginUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only capture if not already set in the current session
        if (!$request->session()->has('origin_url')) {
            // Priority 1: Explicit 'return_to' parameter
            if ($request->has('return_to')) {
                $request->session()->put('origin_url', $request->query('return_to'));
            } 
            // Priority 2: External Referer
            elseif ($request->header('referer')) {
                $referer = $request->header('referer');
                $host = $request->getHost();
                
                // If the referer is from a different host, store it
                if (!str_contains($referer, $host)) {
                    $request->session()->put('origin_url', $referer);
                }
            }
        }

        return $next($request);
    }
}
