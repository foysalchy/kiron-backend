<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PartyActivity;
use App\Models\User;
use App\Notifications\OmnichannelAssignedNotification;
use App\Notifications\OmnichannelUnassignedNotification;
use App\Services\Notification\NotificationRecipientResolver;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $filter = $request->query('filter', 'all');

        // ১. মূল ফিল্টার কুয়েরি
        $query = Conversation::with(['channel', 'customer', 'assignedUser'])
            ->where('company_id', $companyId)
            ->orderByDesc('last_activity_at');

        match ($filter) {
            'unassigned' => $query->whereNull('assigned_user_id')->where('status', '!=', 'closed'),
            'mine' => $query->where('assigned_user_id', $request->user()->id)->where('status', '!=', 'closed'),
            'archived' => $query->where('status', 'closed'),
            'unread' => $query->whereHas('messages', fn($q) => $q->where('from', 'customer')->where('is_read', false)),
            'all' => $query->where('status', '!=', 'closed'),
            default => $query->whereHas('channel', fn($q) => $q->where('slug', $filter)),
        };

        $conversations = $query->get();

        $allCount = Conversation::where('company_id', $companyId)->where('status', '!=', 'closed')->count();
        $unassignedCount = Conversation::where('company_id', $companyId)->whereNull('assigned_user_id')->where('status', '!=', 'closed')->count();
        $mineCount = Conversation::where('company_id', $companyId)->where('assigned_user_id', $request->user()->id)->where('status', '!=', 'closed')->count();
        $archivedCount = Conversation::where('company_id', $companyId)->where('status', 'closed')->count();

        $unreadCount = Message::whereHas('conversation', function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->where('status', '!=', 'closed');
        })
            ->where('from', 'customer')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'conversations' => $conversations,
            'counts' => [
                'all' => $allCount,
                'unassigned' => $unassignedCount,
                'mine' => $mineCount,
                'archived' => $archivedCount,
                'unread' => $unreadCount, // ডাইনামিক আনরিড মেসেজ কাউন্ট
            ]
        ]);
    }
    public function archive(Request $request, Conversation $conversation)
    {
        $newStatus = $conversation->status === 'closed' ? 'open' : 'closed';
        $conversation->update(['status' => $newStatus]);

        $agentName = $request->user()->name;
        $action = $newStatus === 'closed' ? 'archived' : 'reopened';

        PartyActivity::create([
            'party_id' => $conversation->party_id,
            'agent_id' => $request->user()->id,
            'type' => 'conversation',
            'label' => "Chat {$action} by {$agentName}",
            'meta' => $conversation->channel->name ?? 'Omni Inbox'
        ]);

        return response()->json($conversation);
    }

    public function assign(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->company_id === $request->user()->company_id, 403);

        $data = $request->validate(['user_id' => 'required|exists:users,id']);

        $previousUserId = $conversation->assigned_user_id;

        $conversation->update(['assigned_user_id' => $data['user_id']]);

        // ── Notifications ──
        if ($previousUserId != $data['user_id']) {
            $actor = $request->user();
            $isActorCompanySuperAdmin = !is_null($actor->company_id) && is_null($actor->role);

            $companySuperAdmins = collect();
            if (!is_null($conversation->company_id) && !$isActorCompanySuperAdmin) {
                $companySuperAdmins = NotificationRecipientResolver::companySuperAdmin($conversation->company_id);
            }

            // Purano user remove
            if (!empty($previousUserId)) {
                $removedUser = User::find($previousUserId);
                if ($removedUser) {
                    $recipients = collect([$removedUser])->concat($companySuperAdmins);

                    NotificationService::notify(
                        $recipients,
                        new OmnichannelUnassignedNotification(
                            $conversation->id,
                            $conversation->company_id,
                            $removedUser->id,
                        )
                    );
                }
            }

            // Notun user assign
            $assignedUser = User::find($data['user_id']);
            if ($assignedUser) {
                $recipients = collect([$assignedUser])->concat($companySuperAdmins);

                NotificationService::notify(
                    $recipients,
                    new OmnichannelAssignedNotification(
                        $conversation->id,
                        $conversation->company_id,
                        $assignedUser->id,
                        $actor->name,
                    )
                );
            }
        }

        return response()->json($conversation->fresh(['assignedUser']));
    }

    public function messages(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->company_id === $request->user()->company_id, 403);
        if ($request->user()) {
            $conversation->messages()
                ->where('from', 'customer')
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }
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
            $file = $request->file('file');

            $filePath = FileUploadHelper::upload(
                $file,
                'inbox-attachments'
            );

            $fileName = $file->getClientOriginalName();
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
