<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimelineEvent;
use App\Models\TodoItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TodoItemController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->input('filter', 'pending');

        $query = TodoItem::query()->orderBy('due_date')->orderBy('id');

        $query = match ($filter) {
            'due'         => $query->where('is_completed', false)
                                  ->whereNotNull('due_date')
                                  ->where('due_date', '<=', now()->toDateString())
                                  ->where('yearly_goal', 'NA'),
            'bucketlist'  => $query->where('is_bucketlist', true)
                                  ->where('yearly_goal', 'NA'),
            'completed'   => $query->where('is_completed', true)
                                  ->where('yearly_goal', 'NA'),
            'pending'     => $query->where('is_completed', false)
                                  ->where('yearly_goal', 'NA'),
            'archived'    => $query->where('is_completed', true)
                                  ->where(fn ($q) => $q->whereNotNull('cost_try')->orWhereNotNull('time_cost_hours'))
                                  ->where('yearly_goal', 'NA'),
            'yearly_goal' => $query->where('yearly_goal', '!=', 'NA'),
            default       => $query,
        };

        $items = $query->paginate(20)->withQueryString();

        return view('admin.todo-items.index', compact('items', 'filter'));
    }

    public function create(): View
    {
        return view('admin.todo-items.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:500',
            'cost_try'        => 'nullable|numeric|min:0',
            'time_cost_hours' => 'nullable|numeric|min:0',
            'due_date'        => 'nullable|date',
            'is_bucketlist'   => 'boolean',
            'yearly_goal'     => 'nullable|string|max:10',
            'is_completed'    => 'boolean',
            'image'           => 'nullable|image|max:4096',
        ]);

        $data['is_bucketlist'] = $request->boolean('is_bucketlist');
        $data['yearly_goal']   = $data['yearly_goal'] ?? 'NA';
        $data['is_completed']  = $request->boolean('is_completed');

        if ($data['is_completed']) {
            $data['completed_at'] = now();
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('bucketlist', 'public');
        }
        unset($data['image']);

        $item = TodoItem::create($data);

        if ($item->is_bucketlist && $item->is_completed && $item->image_path) {
            TimelineEvent::create([
                'title'       => $item->title,
                'description' => $item->description,
                'event_type'  => 'milestone',
                'start_date'  => $item->completed_at?->toDateString() ?? now()->toDateString(),
                'image'       => $item->image_path,
                'category'    => 'bucketlist',
                'is_public'   => true,
            ]);
        }

        return to_route('admin.todo-items.index')->with('success', __('Todo created.'));
    }

    public function edit(TodoItem $todoItem): View
    {
        return view('admin.todo-items.edit', ['item' => $todoItem]);
    }

    public function update(Request $request, TodoItem $todoItem): RedirectResponse
    {
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:500',
            'cost_try'        => 'nullable|numeric|min:0',
            'time_cost_hours' => 'nullable|numeric|min:0',
            'due_date'        => 'nullable|date',
            'is_bucketlist'   => 'boolean',
            'yearly_goal'     => 'nullable|string|max:10',
            'is_completed'    => 'boolean',
            'image'           => 'nullable|image|max:4096',
        ]);

        $data['is_bucketlist'] = $request->boolean('is_bucketlist');
        $data['yearly_goal']   = $data['yearly_goal'] ?? 'NA';
        $wasCompleted = $todoItem->is_completed;
        $data['is_completed'] = $request->boolean('is_completed');

        if ($data['is_completed'] && ! $wasCompleted) {
            $data['completed_at'] = now();
        } elseif (! $data['is_completed']) {
            $data['completed_at'] = null;
        }

        if ($request->hasFile('image')) {
            if ($todoItem->image_path) {
                Storage::disk('public')->delete($todoItem->image_path);
            }
            $data['image_path'] = $request->file('image')->store('bucketlist', 'public');
        }

        if ($request->boolean('remove_image') && $todoItem->image_path) {
            Storage::disk('public')->delete($todoItem->image_path);
            $data['image_path'] = null;
        }

        unset($data['image']);
        $todoItem->update($data);

        if ($todoItem->is_bucketlist && $todoItem->is_completed && ! $wasCompleted && $todoItem->image_path) {
            TimelineEvent::create([
                'title'       => $todoItem->title,
                'description' => $todoItem->description,
                'event_type'  => 'milestone',
                'start_date'  => $todoItem->completed_at?->toDateString() ?? now()->toDateString(),
                'image'       => $todoItem->image_path,
                'category'    => 'bucketlist',
                'is_public'   => true,
            ]);
        }

        return to_route('admin.todo-items.index')->with('success', __('Todo updated.'));
    }

    public function destroy(TodoItem $todoItem): RedirectResponse
    {
        $todoItem->delete();

        return to_route('admin.todo-items.index')->with('success', __('Todo deleted.'));
    }

    public function toggleComplete(TodoItem $todoItem): JsonResponse|RedirectResponse
    {
        $wasCompleted = $todoItem->is_completed;
        $todoItem->is_completed = ! $wasCompleted;
        $todoItem->completed_at = $todoItem->is_completed ? now() : null;
        $todoItem->save();

        if ($todoItem->is_bucketlist && $todoItem->is_completed && ! $wasCompleted && $todoItem->image_path) {
            TimelineEvent::create([
                'title'       => $todoItem->title,
                'description' => $todoItem->description,
                'event_type'  => 'milestone',
                'start_date'  => $todoItem->completed_at?->toDateString() ?? now()->toDateString(),
                'image'       => $todoItem->image_path,
                'category'    => 'bucketlist',
                'is_public'   => true,
            ]);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'completed'    => $todoItem->is_completed,
                'completed_at' => $todoItem->completed_at?->toDateTimeString(),
            ]);
        }

        return back()->with('success', $todoItem->is_completed ? 'Görev tamamlandı.' : 'Görev geri alındı.');
    }
}
