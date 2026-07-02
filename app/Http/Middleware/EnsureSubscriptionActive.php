<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || $user->is_super_admin) {
            return $next($request);
        }

        if ($user->company && $user->company->hasActiveAccess()) {
            return $next($request);
        }

        return response()->json([
            'success'         => false,
            'message'         => 'Your trial or subscription has expired. Please renew your plan to continue.',
            'action_required' => 'BILLING_REQUIRED',
        ], 403);
    }
}