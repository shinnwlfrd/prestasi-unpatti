<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureOriginUrl
{
    private function isSafeRedirect(?string $url): bool
    {
        return is_string($url) && str_starts_with($url, '/');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only capture if not already set in the current session
        if (!$request->session()->has('origin_url')) {
            $returnTo = $request->query('return_to');

            if ($this->isSafeRedirect($returnTo)) {
                $request->session()->put('origin_url', $returnTo);
            }
        }

        return $next($request);
    }
}
