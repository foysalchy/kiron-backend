<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\Status;

class CheckUserAccessStatus
{
    public function handle(Request $request, Closure $next, string $mode = 'strict')
    {
        $user = $request->user();

        // 1. Always block Draft users (Draft Subscription Modal will be triggered on frontend)
        if ($user->status === Status::Draft->value) {
            return response()->json([
                'success' => false,
                'action_required' => 'SUBSCRIPTION_REQUIRED',
                'message' => 'You currently have not subscribed to any of our packages.'
            ], 403);
        }

        // 2. Block Pending users ONLY IF the route is 'strict'
        if ($user->status === Status::Pending->value && $mode !== 'allow_pending') {
            return response()->json([
                'success' => false,
                'action_required' => 'BASIC_SETTINGS_REQUIRED',
                'message' => 'Please complete the basic settings to active the account.',
                'redirect_url' => '/site-settings'
            ], 403);
        }

        // 3. Active users (or Pending users on 'allow_pending' routes) proceed normally
        return $next($request);
    }
}