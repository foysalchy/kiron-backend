<?php

namespace App\Services;

use App\Models\ActionLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActionLogService
{
    // action      = category slug  e.g. "created", "updated", "status_changed", "deleted"
    // action_type = human-readable message e.g. "Kalam Ahmed user is updated"

    private const ACTION_CONFIG = [
        'created'        => ['label' => 'Created',        'color' => 'green'],
        'updated'        => ['label' => 'Updated',        'color' => 'blue'],
        'deleted'        => ['label' => 'Deleted',        'color' => 'red'],
        'restored'       => ['label' => 'Restored',       'color' => 'teal'],
        'status_changed' => ['label' => 'Status Changed', 'color' => 'orange'],
        'login'          => ['label' => 'Login',          'color' => 'purple'],
        'logout'         => ['label' => 'Logout',         'color' => 'gray'],
        'exported'       => ['label' => 'Exported',       'color' => 'yellow'],
        'imported'       => ['label' => 'Imported',       'color' => 'cyan'],
        'viewed'         => ['label' => 'Viewed',         'color' => 'slate'],
        'assigned'       => ['label' => 'Assigned',       'color' => 'indigo'],
        'converted'      => ['label' => 'Converted',      'color' => 'pink'],
        'approved'       => ['label' => 'Approved',       'color' => 'green'],
        'rejected'       => ['label' => 'Rejected',       'color' => 'red'],
    ];

    public function getAll(Request $request)
    {
        $query = ActionLog::with(['user:id,name,email'])
            ->select([
                'id',
                'user_id',
                'action_id',
                'action',
                'action_type',
                'module',
                'created_at',
            ]);

        $query
            ->when(
                $request->user_id,
                fn($q) => $q->where('user_id', $request->user_id)
            )
            ->when(
                $request->module,
                fn($q) => $q->where('module', $request->module)
            )
            ->when(
                $request->action,
                fn($q) => $q->where('action', $request->action)
            )
            ->when(
                $request->search,
                fn($q) => $q->where(function ($q2) use ($request) {
                    $q2->where('action_type', 'like', "%{$request->search}%")
                        ->orWhere('module', 'like', "%{$request->search}%");
                })
            )
            ->when(
                $request->start_date,
                // support both date-only and datetime (from login history view)
                fn($q) => $q->where('created_at', '>=', $request->start_date)
            )
            ->when(
                $request->end_date,
                fn($q) => $q->where('created_at', '<=', $request->end_date)
            );

        $logs = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        $logs->getCollection()->transform(fn($log) => $this->processLog($log));

        return $logs;
    }

    public function getModules(): array
    {
        return ActionLog::select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->toArray();
    }

    public function getActions(): array
    {
        return ActionLog::select('action')
            ->distinct()
            ->whereNotNull('action')
            ->orderBy('action')
            ->pluck('action')
            ->toArray();
    }

    // Returns users who have action log entries — for the user filter dropdown
    public function getUsersWithLogs(): array
    {
        return User::whereIn('id', ActionLog::select('user_id')->distinct())
            ->select('id', 'name', 'email')
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    private function processLog(ActionLog $log): ActionLog
    {
        $key    = strtolower(trim($log->action ?? ''));
        $config = self::ACTION_CONFIG[$key] ?? null;

        $log->badge_color   = $config['color'] ?? 'gray';
        $log->action_label  = $config['label'] ?? ucwords(str_replace('_', ' ', $log->action ?? 'Action'));

        return $log;
    }
}
