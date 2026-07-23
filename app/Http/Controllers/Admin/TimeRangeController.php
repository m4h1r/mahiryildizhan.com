<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeRange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeRangeController extends Controller
{
    public function sync(Request $request, int $dayOfWeek): RedirectResponse
    {
        abort_unless($dayOfWeek >= 0 && $dayOfWeek <= 6, 404);

        $validated = $request->validate([
            'ranges' => ['array'],
            'ranges.*.starts_at' => ['required', 'date_format:H:i'],
            'ranges.*.ends_at' => ['required', 'date_format:H:i'],
            'ranges.*.label' => ['required', 'string', 'max:60'],
            'ranges.*.color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        DB::transaction(function () use ($dayOfWeek, $validated): void {
            TimeRange::query()->where('day_of_week', $dayOfWeek)->delete();

            $rows = collect($validated['ranges'] ?? [])->map(fn (array $range) => [
                'day_of_week' => $dayOfWeek,
                'starts_at' => $range['starts_at'],
                'ends_at' => $range['ends_at'],
                'label' => $range['label'],
                'color' => $range['color'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

            if ($rows !== []) {
                TimeRange::query()->insert($rows);
            }
        });

        return to_route('admin.settings', ['tab' => 'time_ranges'])->with('success', 'Zaman aralıkları güncellendi.');
    }
}
