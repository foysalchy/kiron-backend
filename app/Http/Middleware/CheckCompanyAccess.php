<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Add helper methods to request
        $request->macro('isSuperAdmin', function () use ($user) {
            return $user->role === 'super_admin';
        
        });

        $request->macro('getCompanyId', function () use ($user) {
            return $user->company_id;
        });

        $request->macro('canAccessCompany', function ($companyId) use ($user) {
            // Super admin can access any company
            if ($user->role === 'super_admin') {
                return true;
            }
            
            // Regular user can only access their own company
            return $user->company_id == $companyId;
        });

        return $next($request);
    }
}