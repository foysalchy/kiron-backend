<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Label;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function index(Request $request)
    {
        $channels = Channel::where('company_id', $request->user()->company_id)
            ->withCount(['conversations as unread_count' => function ($q) {
                $q->whereHas('messages', fn($m) => $m->where('from', 'customer')->where('is_read', false));
            }])
            ->get();

        return response()->json($channels);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:channels,slug',
            'type' => 'required|in:facebook,instagram,whatsapp,telegram,livechat,email,custom',
            'color' => 'nullable|string|max:20',
        ]);

        $channel = Channel::create([
            ...$data,
            'company_id' => $request->user()->company_id,
        ]);

        return response()->json($channel, 201);
    }

    public function update(Request $request, Channel $channel)
    {
        $this->authorizeCompany($request, $channel);
        $channel->update($request->only(['name', 'color', 'is_active']));
        return response()->json($channel);
    }

    public function destroy(Request $request, Channel $channel)
    {
        $this->authorizeCompany($request, $channel);
        $channel->delete();
        return response()->json(['message' => 'Channel deleted']);
    }

    private function authorizeCompany(Request $request, Channel $channel)
    {
        abort_unless($channel->company_id === $request->user()->company_id, 403);
    }
    public function label(Request $request)
    {
        $labels = Label::where('company_id', $request->user()->company_id)->get();
        return response()->json($labels);
    }

    public function labelStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string',
        ]);

        $label = Label::create([
            'name' => $data['name'],
            'color' => $data['color'],

        ]);

        return response()->json($label, 201);
    }
    public function labelUpdate(Request $request, Label $label)
    {
        abort_unless($label->company_id === $request->user()->company_id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required',
        ]);

        $label->update($data);
        return response()->json($label);
    }

    public function labelDestroy(Request $request, Label $label)
    {
        abort_unless($label->company_id === $request->user()->company_id, 403);
        $label->delete();
        return response()->json(['message' => 'Label deleted']);
    }
}
