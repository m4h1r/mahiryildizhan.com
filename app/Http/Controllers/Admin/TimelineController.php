<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimelineEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TimelineController extends Controller
{
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
        $payload['color'] = $payload['color'] ?: '#3B82F6';
        $payload['tags'] = $payload['tags'] ? array_values(array_filter(array_map('trim', explode(',', $payload['tags'])))) : null;
        $payload['metadata'] = $payload['metadata'] ? json_decode((string) $payload['metadata'], true) : null;

        if ($payload['metadata'] !== null && ! is_array($payload['metadata'])) {
            abort(422, 'Metadata must be valid JSON object.');
        }

        return $payload;
    }
}