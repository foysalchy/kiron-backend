<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReminderSettingController extends Controller
{
    public function index()
    {
        $settings = ReminderSetting::query()
            ->orderBy('type')
            ->orderBy('days_before', 'desc')
            ->get();

        return response()->json([
            'message' => 'Reminder settings fetched successfully',
            'data' => $settings,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSetting($request);

        // Same type + same days_before duplicate hote debe na
        $exists = ReminderSetting::where('type', $validated['type'])
            ->where('days_before', $validated['days_before'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'A reminder for this type and day count already exists.',
            ], 422);
        }

        $setting = ReminderSetting::create($validated);

        return response()->json([
            'message' => 'Reminder setting created successfully',
            'data' => $setting,
        ], 201);
    }

    public function update(Request $request, ReminderSetting $reminderSetting)
    {
        $validated = $this->validateSetting($request);

        $reminderSetting->update($validated);

        return response()->json([
            'message' => 'Reminder setting updated successfully',
            'data' => $reminderSetting,
        ]);
    }

    public function destroy(ReminderSetting $reminderSetting)
    {
        $reminderSetting->delete();

        return response()->json(['message' => 'Reminder setting deleted successfully']);
    }

    protected function validateSetting(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'type'        => 'required|in:trial,subscription',
            'days_before' => 'required|integer|min:1|max:90',
            'status'      => 'required|boolean',
        ]);

        if ($validator->fails()) {
            abort(response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422));
        }

        return $validator->validated();
    }
}
