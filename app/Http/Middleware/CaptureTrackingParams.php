<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureTrackingParams
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('fbclid')) {
            $fbclid = $request->query('fbclid');
            $timestamp = time() * 1000;
            $fbc = "fb.1.{$timestamp}.{$fbclid}";
            session()->put('_fbc', $fbc);
        }

        if ($request->has('ttclid')) {
            session()->put('_ttp', $request->query('ttclid')); // basic fallback
        }

        return $next($request);
    }
}
