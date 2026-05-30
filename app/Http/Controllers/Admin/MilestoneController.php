<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MilestoneController extends Controller
{
    public function index(): View
    {
        $milestones = Milestone::with('milestoneable')
            ->orderByDesc('achieved_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.milestones.index', compact('milestones'));
    }

    public function edit(Milestone $milestone): View
    {
        return view('admin.milestones.edit', compact('milestone'));
    }

    public function update(Request $request, Milestone $milestone): RedirectResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'achieved_at' => 'nullable|date',
            'image'       => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('image')) {
            if ($milestone->image_path) {
                Storage::disk('public')->delete($milestone->image_path);
            }
            $data['image_path'] = $request->file('image')->store('milestones', 'public');
        }

        if ($request->boolean('remove_image') && $milestone->image_path) {
            Storage::disk('public')->delete($milestone->image_path);
            $data['image_path'] = null;
        }

        unset($data['image']);
        $milestone->update($data);

        return to_route('admin.milestones.index')->with('success', 'Kilometre taşı güncellendi.');
    }

    public function destroy(Milestone $milestone): RedirectResponse
    {
        if ($milestone->image_path) {
            Storage::disk('public')->delete($milestone->image_path);
        }
        $milestone->delete();

        return to_route('admin.milestones.index')->with('success', 'Kilometre taşı silindi.');
    }
}
