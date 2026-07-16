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
        // কোম্পানি স্কোপসহ সমস্ত অ্যাক্টিভ চ্যানেল লোড করা হলো
        $channels = Channel::all();

        // লুপ চালিয়ে শুধুমাত্র কাস্টম (লোকাল) চ্যানেলের জন্য আনরিড মেসেজ কাউন্ট করা হচ্ছে
        // ফেসবুক বা মেটা চ্যানেলের ক্ষেত্রে কোনো ডাটাবেজ কুয়েরি স্পর্শ করা হবে না (যেহেতু এগুলো রিয়েল-টাইমে মেটা থেকে লোড হয়)
        foreach ($channels as $channel) {
            if (is_null($channel->channel_group_id)) {
                // কাস্টম চ্যানেলের ক্ষেত্রে ডাটাবেজ থেকে আনরিড কাউন্ট
                $channel->unread_count = $channel->conversations()
                    ->whereHas('messages', function ($q) {
                        $q->where('from', 'customer')->where('is_read', false);
                    })
                    ->count();
            } else {
                // মেটা বা ফেসবুক চ্যানেলের জন্য ডিফল্ট কাউন্ট ০ (কারণ চ্যাট লিস্ট রিয়েল-টাইমে মেটা থেকে আসবে)
                $channel->unread_count = 0;
            }
        }

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
