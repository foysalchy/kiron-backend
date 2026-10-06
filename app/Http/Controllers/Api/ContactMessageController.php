<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $query = ContactMessage::where('company_id', $companyId);

        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->where('is_read', 0);
            } elseif ($request->filter === 'read') {
                $query->where('is_read', 1);
            }
        }

        $perPage = $request->input('per_page', 15);
        
        $paginator = $query->orderBy('is_read', 'asc')
                           ->latest()
                           ->paginate($perPage);

        return response()->json(['data' => $paginator]);
    }

    public function unreadCount(Request $request)
    {
        $companyId = $request->user()->company_id;
        $count = ContactMessage::where('company_id', $companyId)->where('is_read', 0)->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(Request $request, $id)
    {
        $companyId = $request->user()->company_id;
        $message = ContactMessage::where('company_id', $companyId)->findOrFail($id);
        $message->update(['is_read' => 1]);

        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        $companyId = $request->user()->company_id;
        ContactMessage::where('company_id', $companyId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json(['message' => 'All marked as read']);
    }
    
    public function destroy(Request $request, $id)
    {
        $companyId = $request->user()->company_id;
        $message = ContactMessage::where('company_id', $companyId)->findOrFail($id);
        $message->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
