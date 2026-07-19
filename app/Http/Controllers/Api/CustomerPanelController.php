<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\CrmNote;
use App\Models\Party;
use App\Models\PartyActivity;
use Illuminate\Http\Request;

class CustomerPanelController extends Controller
{

    protected function logActivity($partyId, $type, $label, $meta = null)
    {
        PartyActivity::create([
            'party_id' => $partyId,
            'agent_id' => auth()->id(),
            'type' => $type,
            'label' => $label,
            'meta' => $meta,
        ]);
    }


    public function addLabel(Request $request, $id)
    {
        $data = $request->validate([
            'label_id' => 'required|exists:labels,id'
        ]);

        $label = \App\Models\Label::findOrFail($data['label_id']);
        $party = Party::with(['labels', 'crmNotes'])->where('fb_psid', $id)->first();

        $party->labels()->syncWithoutDetaching([$label->id]);

        $agentName = $request->user()->name;
        $this->logActivity(
            $party->id,
            'activity',
            "Label '{$label->name}' added by {$agentName}",
            $label->color
        );

        return response()->json($party->load('labels'));
    }

    public function addNote(Request $request, $id)
    {
        $data = $request->validate([
            'text' => 'required|string'
        ]);
        $party = Party::with(['labels', 'crmNotes'])->where('fb_psid', $id)->first();


        $note = $party->crmNotes()->create([
            'text' => $data['text'],
            'agent_id' => $request->user()->id
        ]);


        $agentName = $request->user()->name;
        $this->logActivity(
            $party->id,
            'note',
            "CRM Note added by {$agentName}",
            str($data['text'])->limit(40)
        );

        return response()->json($note->load('agent'));
    }
    public function show($id)
    {
        $party = Party::with(['labels', 'crmNotes'])->where('fb_psid', $id)->first();

        if (!$party) {
            return response()->json([
                'error' => 'Party or Customer details not found for this.'
            ], 404);
        }

        $party->load(['crmNotes']);

        $orderCount = $party->orders()->count();
        $totalSpent = $party->orders()->where('status', Status::Delivered->value)->sum('grand_total');
        $lastOrder = $party->orders()->latest()->first();

        $timeline = PartyActivity::where('party_id', $party->id)
            ->latest()
            ->get()
            ->map(function ($act) {
                return [
                    'type' => $act->type,
                    'label' => $act->label,
                    'meta' => $act->meta,
                    'created_at_human' => $act->created_at->diffForHumans()
                ];
            })
            ->toArray();
        if ($lastOrder) {
            array_unshift($timeline, [
                'type' => 'order',
                'label' => "Latest Order #{$lastOrder->id} placed",
                'meta' => "৳ " . number_format($lastOrder->amount, 2),
                'created_at_human' => $lastOrder->created_at->diffForHumans()
            ]);
        }

        $lastNote = $party->crmNotes->first();
        if ($lastNote) {
            $timeline[] = [
                'type' => 'note',
                'label' => "CRM Note added by " . ($lastNote->agent->name ?? 'Agent'),
                'meta' => str($lastNote->text)->limit(50),
                'created_at_human' => $lastNote->created_at->diffForHumans()
            ];
        }

        return response()->json([
            'profile' => [
                'id' => $party->id,
                'type' => $party->type, // 1 = Supplier, 2 = Customer
                'name' => $party->name,
                'email' => $party->email,
                'phone' => $party->phone,
                'alternative_phone' => $party->alternative_phone,
                'profile_pic' => $party->profile,
                'address' => implode(', ', array_filter([$party->thana, $party->district, $party->division])),
                'balance' => $party->balance,
                'due_amount' => $party->due_amount,
                'created_at_human' => $party->created_at->diffForHumans()
            ],
            'labels' => $party->labels,
            'crm_notes' => $party->crmNotes,
            'order_summary' => [
                'count' => $orderCount,
                'spent' => "৳ " . number_format($totalSpent, 2),
                'last' => $lastOrder ? $lastOrder->created_at->diffForHumans() : 'No orders yet'
            ],
            'timeline' => $timeline
        ]);
    }

    public function noteUpdate(Request $request, $id)
    {
        $note = CrmNote::findOrFail($id);

        $data = $request->validate([
            'text' => 'required|string'
        ]);

        $note->update([
            'text' => $data['text']
        ]);
        $agentName = $request->user()->name;
        $this->logActivity(
            $note->party_id,
            'note',
            "CRM Note updated by {$agentName}",
            str($data['text'])->limit(40)
        );

        return response()->json($note);
    }

    public function noteDestroy(Request $request, $id)
    {
        $note = CrmNote::findOrFail($id);
        $note->delete();
        $agentName = $request->user()->name;
        $this->logActivity(
            $id,
            'activity',
            "CRM Note deleted by {$agentName}"
        );
        return response()->json(['message' => 'Note deleted successfully']);
    }
}
