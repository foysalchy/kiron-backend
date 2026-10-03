<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->notifications();

        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->filter === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        $perPage = $request->input('per_page', 15);
        $paginator = $query->latest()->paginate($perPage);

        $paginator->getCollection()->transform(function ($n) {
            return [
                'id'         => $n->id,
                'type'       => $n->data['type'] ?? null,
                'title'      => $n->data['title'] ?? null,
                'message'    => $n->data['message'] ?? null,
                'action_url' => $n->data['action_url'] ?? null,
                'is_read'    => !is_null($n->read_at),
                'created_at' => $n->created_at,
            ];
        });

        return response()->json(['data' => $paginator]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'message'    => 'Notification marked as read',
            'action_url' => $notification->data['action_url'] ?? null,
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
