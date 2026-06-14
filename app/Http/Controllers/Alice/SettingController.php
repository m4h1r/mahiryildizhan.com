<?php

namespace App\Http\Controllers\Alice;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $settings = Setting::query()
            ->when($request->query('group'), fn ($q, $group) => $q->where('group', $group))
            ->when(! $request->boolean('include_secret'), fn ($q) => $q->where('is_secret', false))
            ->get(['id', 'key', 'value', 'group', 'is_secret', 'description']);

        return response()->json(['data' => $settings]);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->first();
        if (! $setting) {
            return $this->notFound("'{$key}' anahtarına sahip ayar bulunamadı");
        }

        $request->validate(['value' => 'required']);

        $this->storeAuditOldData($request, $setting);
        $setting->update(['value' => $request->input('value')]);

        return $this->success([
            'key' => $setting->key,
            'value' => $setting->value,
            'group' => $setting->group,
        ]);
    }
}
