<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimelineEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TimelineController extends Controller
{
    public function visualize(): View
    {
        $events = TimelineEvent::query()
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        $containerHeight = 600;
        $yearMarkers = [];

        if ($events->isNotEmpty()) {
            $allDates = $events->flatMap(function ($event): array {
                $dates = [$event->start_date];
                if ($event->end_date) {
                    $dates[] = $event->end_date;
                }

                return $dates;
            })->sortBy(fn ($d) => $d->unix())->values();

            $earliest = $allDates->first()->copy()->subDays(45);
            $latest = $allDates->last()->copy()->addDays(45);
            $totalDays = max(1, $earliest->diffInDays($latest));

            $usable = (int) min(4000, max(900, $totalDays * 4));
            $containerHeight = $usable + 120;
            $topPad = 60;

            $toPx = fn (Carbon $date): int => $topPad + (int) round(
                $earliest->diffInDays($date) / $totalDays * $usable
            );

            $events = $events->map(function ($event) use ($toPx) {
                $startPx = $toPx($event->start_date);
                $endPx = $event->end_date ? $toPx($event->end_date) : null;
                $heightPx = $endPx !== null ? max(24, $endPx - $startPx) : null;
                $isProc = $event->event_type === 'process' && $heightPx !== null;
                $cardPx = $isProc ? (int) ($startPx + $heightPx / 2) : $startPx;

                $event->setAttribute('start_px', $startPx);
                $event->setAttribute('end_px', $endPx);
                $event->setAttribute('height_px', $heightPx);
                $event->setAttribute('card_px', $cardPx);

                return $event;
            })->values();

            // Prevent cards on the same side from overlapping
            $minCardGap = 200;
            foreach ([0, 1] as $side) {
                $prev = null;
                foreach ($events->filter(fn ($e, $k) => $k % 2 === $side)->values() as $event) {
                    if ($prev !== null && ($event->card_px - $prev->card_px) < $minCardGap) {
                        $event->setAttribute('card_px', $prev->card_px + $minCardGap);
                    }
                    $prev = $event;
                }
            }

            // Grow container to fit pushed-down cards
            $maxCardPx = $events->max(fn ($e) => $e->card_px);
            if ($maxCardPx + 80 > $containerHeight) {
                $containerHeight = $maxCardPx + 80;
            }

            for ($y = (int) $earliest->format('Y'); $y <= (int) $latest->format('Y'); $y++) {
                $d = Carbon::create($y, 1, 1);
                if ($d->between($earliest, $latest)) {
                    $yearMarkers[] = ['year' => $y, 'px' => $toPx($d)];
                }
            }
        }

        return view('admin.timeline.visualize', [
            'title' => 'Timeline',
            'heading' => 'Timeline',
            'events' => $events,
            'containerHeight' => $containerHeight,
            'yearMarkers' => $yearMarkers,
        ]);
    }

    public function index(Request $request): View
    {
        $query = TimelineEvent::query()->latest('start_date')->latest('id');

        if ($eventType = $request->string('event_type')->toString()) {
            $query->where('event_type', $eventType);
        }

        if ($request->has('is_public') && $request->input('is_public') !== '') {
            $query->where('is_public', $request->boolean('is_public'));
        }

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return view('admin.timeline.index', [
            'title' => 'Timeline',
            'heading' => 'Timeline',
            'events' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'event_type', 'is_public']),
        ]);
    }

    public function create(): View
    {
        return view('admin.timeline.create', [
            'title' => 'New Timeline Event',
            'heading' => 'New Timeline Event',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TimelineEvent::query()->create($this->validatedPayload($request));

        return to_route('admin.timeline.index')->with('success', 'Timeline event created.');
    }

    public function edit(TimelineEvent $timeline): View
    {
        return view('admin.timeline.edit', [
            'title' => 'Edit Timeline Event',
            'heading' => 'Edit Timeline Event',
            'event' => $timeline,
        ]);
    }

    public function update(Request $request, TimelineEvent $timeline): RedirectResponse
    {
        $timeline->update($this->validatedPayload($request));

        return to_route('admin.timeline.index')->with('success', 'Timeline event updated.');
    }

    public function destroy(TimelineEvent $timeline): RedirectResponse
    {
        $timeline->delete();

        return to_route('admin.timeline.index')->with('success', 'Timeline event deleted.');
    }

    private function validatedPayload(Request $request): array
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', Rule::in(['milestone', 'process'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'image' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'],
            'is_public' => ['nullable', 'boolean'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string'],
            'metadata' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ]);

        $payload['is_public'] = $request->boolean('is_public');
        $payload['order'] = (int) ($payload['order'] ?? 0);
        $payload['color'] = ($payload['color'] ?? null) ?: '#3B82F6';
        $payload['tags'] = ($payload['tags'] ?? null) ? array_values(array_filter(array_map('trim', explode(',', $payload['tags'])))) : null;
        $payload['metadata'] = ($payload['metadata'] ?? null) ? json_decode((string) $payload['metadata'], true) : null;

        if ($payload['metadata'] !== null && ! is_array($payload['metadata'])) {
            abort(422, 'Metadata must be valid JSON object.');
        }

        return $payload;
    }
}
