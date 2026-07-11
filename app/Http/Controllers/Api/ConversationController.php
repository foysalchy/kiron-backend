<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $filter = $request->query('filter', 'all');

        $query = Conversation::with(['channel', 'customer', 'assignedUser'])
            ->orderByDesc('last_activity_at');

        match ($filter) {
            'unassigned' => $query->whereNull('assigned_user_id'),
            'mine' => $query->where('assigned_user_id', $request->user()->id),
            'archived' => $query->where('status', 'closed'),
            'all' => $query,
            default => $query->whereHas('channel', fn($q) => $q->where('slug', $filter)),
        };

        return response()->json($query->get());
    }

    public function assign(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->company_id === $request->user()->company_id, 403);

        $data = $request->validate(['user_id' => 'required|exists:users,id']);

        $conversation->update(['assigned_user_id' => $data['user_id']]);

        return response()->json($conversation->fresh(['assignedUser']));
    }

    public function messages(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->company_id === $request->user()->company_id, 403);

        return response()->json(
            $conversation->messages()->with('agent')->orderBy('created_at')->get()
        );
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->company_id === $request->user()->company_id, 403);

        $data = $request->validate([
            'type' => 'required|in:text,image,document,audio,note',
            'text' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ]);

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('inbox-attachments', 'public');
            $fileName = $request->file('file')->getClientOriginalName();
        }

        $message = $conversation->messages()->create([
            'from' => 'agent',
            'type' => $data['type'],
            'text' => $data['text'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'agent_id' => $request->user()->id,
            'is_read' => true,
        ]);

        $conversation->update(['last_activity_at' => now()]);

        // TODO: dispatch job to actually push this out via the channel's provider (Meta Graph API / WhatsApp Cloud API)

        return response()->json($message->load('agent'), 201);
    }
}
