<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseItem;
use App\Models\TimelineEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PurchaseItemController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->input('filter', 'all');

        $query = PurchaseItem::query()->latest();

        $query = match ($filter) {
            'bucketlist' => $query->where('is_bucketlist', true),
            'completed'  => $query->where('is_completed', true),
            'pending'    => $query->where('is_completed', false),
            default      => $query,
        };

        $items = $query->paginate(20)->withQueryString();

        return view('admin.purchase-items.index', compact('items', 'filter'));
    }

    public function create(): View
    {
        return view('admin.purchase-items.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'cost_try'        => 'nullable|numeric|min:0',
            'time_cost_hours' => 'nullable|numeric|min:0',
            'is_bucketlist'   => 'boolean',
            'is_completed'    => 'boolean',
            'image'           => 'nullable|image|max:4096',
        ]);

        $data['is_bucketlist'] = $request->boolean('is_bucketlist');
        $data['is_completed']  = $request->boolean('is_completed');

        if ($data['is_completed']) {
            $data['completed_at'] = now();
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('bucketlist', 'public');
        }
        unset($data['image']);

        $item = PurchaseItem::create($data);

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

        return to_route('admin.purchase-items.index')->with('success', __('Item created.'));
    }

    public function edit(PurchaseItem $purchaseItem): View
    {
        return view('admin.purchase-items.edit', ['item' => $purchaseItem]);
    }

    public function update(Request $request, PurchaseItem $purchaseItem): RedirectResponse
    {
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'cost_try'        => 'nullable|numeric|min:0',
            'time_cost_hours' => 'nullable|numeric|min:0',
            'is_bucketlist'   => 'boolean',
            'is_completed'    => 'boolean',
            'image'           => 'nullable|image|max:4096',
        ]);

        $data['is_bucketlist'] = $request->boolean('is_bucketlist');
        $wasCompleted = $purchaseItem->is_completed;
        $data['is_completed'] = $request->boolean('is_completed');

        if ($data['is_completed'] && ! $wasCompleted) {
            $data['completed_at'] = now();
        } elseif (! $data['is_completed']) {
            $data['completed_at'] = null;
        }

        if ($request->hasFile('image')) {
            if ($purchaseItem->image_path) {
                Storage::disk('public')->delete($purchaseItem->image_path);
            }
            $data['image_path'] = $request->file('image')->store('bucketlist', 'public');
        }

        if ($request->boolean('remove_image') && $purchaseItem->image_path) {
            Storage::disk('public')->delete($purchaseItem->image_path);
            $data['image_path'] = null;
        }

        unset($data['image']);
        $purchaseItem->update($data);

        if ($purchaseItem->is_bucketlist && $purchaseItem->is_completed && ! $wasCompleted && $purchaseItem->image_path) {
            TimelineEvent::create([
                'title'       => $purchaseItem->title,
                'description' => $purchaseItem->description,
                'event_type'  => 'milestone',
                'start_date'  => $purchaseItem->completed_at?->toDateString() ?? now()->toDateString(),
                'image'       => $purchaseItem->image_path,
                'category'    => 'bucketlist',
                'is_public'   => true,
            ]);
        }

        return to_route('admin.purchase-items.index')->with('success', __('Item updated.'));
    }

    public function destroy(PurchaseItem $purchaseItem): RedirectResponse
    {
        $purchaseItem->delete();

        return to_route('admin.purchase-items.index')->with('success', __('Item deleted.'));
    }

    public function toggleComplete(PurchaseItem $purchaseItem): JsonResponse|RedirectResponse
    {
        $wasCompleted = $purchaseItem->is_completed;
        $purchaseItem->is_completed = ! $wasCompleted;
        $purchaseItem->completed_at = $purchaseItem->is_completed ? now() : null;
        $purchaseItem->save();

        if ($purchaseItem->is_bucketlist && $purchaseItem->is_completed && ! $wasCompleted && $purchaseItem->image_path) {
            TimelineEvent::create([
                'title'       => $purchaseItem->title,
                'description' => $purchaseItem->description,
                'event_type'  => 'milestone',
                'start_date'  => $purchaseItem->completed_at?->toDateString() ?? now()->toDateString(),
                'image'       => $purchaseItem->image_path,
                'category'    => 'bucketlist',
                'is_public'   => true,
            ]);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'completed'    => $purchaseItem->is_completed,
                'completed_at' => $purchaseItem->completed_at?->toDateTimeString(),
            ]);
        }

        return back()->with('success', $purchaseItem->is_completed ? 'Alım tamamlandı.' : 'Alım geri alındı.');
    }
}
