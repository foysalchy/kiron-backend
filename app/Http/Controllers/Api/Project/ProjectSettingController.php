<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $setting = ProjectSetting::first();
        if (!$setting) {
            $setting = ProjectSetting::create([
                'project_prefix' => 'PRJ-',
                'task_prefix' => 'TSK-',
            ]);
        }
        return ResponseHelper::success($setting, 'Project settings retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $setting = ProjectSetting::first();
        if (!$setting) {
            $setting = new ProjectSetting();
        }

        $setting->fill($request->only([
            'project_prefix',
            'task_prefix',
            'custom_statuses',
            'default_budgets',
        ]));
        $setting->save();

        return ResponseHelper::success($setting, 'Project settings saved');
    }
}
