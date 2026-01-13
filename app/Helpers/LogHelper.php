<?php

namespace App\Helpers;

use App\Models\ActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogHelper
{

    public static function log(
        string $action,
        string $module,
        int $actionId,
        int $companyId,
        ?int $userId = null
    ): ?ActionLog {
        try {
            return ActionLog::create([
                'company_id' => $companyId,
                'user_id' => $userId ?? Auth::id(),
                'action_id' => $actionId,
                'action' => $action,
                'module' => $module,
            ]);
        } catch (\Exception $e) {
            // Silent fail - don't break the main operation
            Log::error('Action log failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log created action
     */
    public static function created(
        string $module,
        int $actionId,
        int $companyId,
        ?int $userId = null
    ): ?ActionLog {
        return self::log('created', $module, $actionId, $companyId, $userId);
    }

    /**
     * Log updated action
     */
    public static function updated(
        string $module,
        int $actionId,
        int $companyId,
        ?int $userId = null

    ): ?ActionLog {
        return self::log('updated', $module, $actionId, $companyId, $userId);
    }

    /**
     * Log deleted action
     */
    public static function deleted(
        string $module,
        int $actionId,
        int $companyId,
        ?int $userId = null

    ): ?ActionLog {
        return self::log('deleted', $module, $actionId, $companyId, $userId);
    }

    /**
     * Log restored action
     */
    public static function restored(
        string $module,
        int $actionId,
        int $companyId,
        ?int $userId = null

    ): ?ActionLog {
        return self::log('restored', $module, $actionId, $companyId, $userId);
    }

    /**
     * Log force deleted action
     */
    public static function forceDeleted(
        string $module,
        int $actionId,
        int $companyId,
        ?int $userId = null

    ): ?ActionLog {
        return self::log('force_deleted', $module, $actionId, $companyId, $userId);
    }

    /**
     * Log status change action
     */
    public static function statusChanged(
        string $module,
        int $actionId,
        int $companyId,
        ?int $userId = null

    ): ?ActionLog {
        return self::log('status_changed', $module, $actionId, $companyId, $userId);
    }

    /**
     * Log custom action
     */
    public static function custom(
        string $action,
        string $module,
        int $actionId,
        int $companyId,
        ?int $userId = null

    ): ?ActionLog {
        return self::log($action, $module, $actionId, $companyId, $userId);
    }

    /**
     * Get activity logs for a module
     */
    public static function getByModule(string $module, ?int $companyId = null, int $limit =20 )
    {
        $query = ActionLog::with(['user', 'company'])
            ->where('module', $module)
            ->latest();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get activity logs for a specific record
     */
    public static function getByRecord( int $actionId, int $limit = 20)
    {
        return ActionLog::with(['user', 'company'])
            ->where('action_id', $actionId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent activity logs
     */
    public static function getRecent(?int $companyId = null, ?int $userId = null, int $days = 7, int $limit = 100)
    {
        $query = ActionLog::with(['user', 'company'])
            ->where('created_at', '>=', now()->subDays($days))
            ->latest();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get user activity logs
     */
    public static function getByUser(int $userId, int $limit = 50)
    {
        return ActionLog::with(['company'])
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Clean old logs
     */
    public static function cleanOldLogs(int $days = 90): int
    {
        return ActionLog::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Get statistics
     */
public static function getStats(?int $companyId = null, ?int $userId = null, int $days = 30): array
{
    $query = ActionLog::where('created_at', '>=', now()->subDays($days));

    if ($companyId) {
        $query->where('company_id', $companyId);
    }

    if ($userId) {
        $query->where('user_id', $userId);
    }

  
    $actionCounts = (clone $query)
        ->selectRaw('action, COUNT(*) as count')
        ->groupBy('action')
        ->pluck('count', 'action')
        ->toArray();

   
    $moduleCounts = (clone $query)
        ->selectRaw('module, COUNT(*) as count')
        ->groupBy('module')
        ->pluck('count', 'module')
        ->toArray();

    return [
        'total_actions' => array_sum($actionCounts),
        'created' => $actionCounts['created'] ?? 0,
        'updated' => $actionCounts['updated'] ?? 0,
        'deleted' => $actionCounts['deleted'] ?? 0,
        'by_module' => $moduleCounts,
        'by_action' => $actionCounts,
    ];
}

}
