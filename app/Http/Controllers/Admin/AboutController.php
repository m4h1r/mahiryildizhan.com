<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        return view('admin.about', [
            'title'   => 'About Page',
            'heading' => 'About Page',
            'content_en' => Setting::get('about_content_en', ''),
            'content_tr' => Setting::get('about_content_tr', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content_en' => ['nullable', 'string'],
            'content_tr' => ['nullable', 'string'],
        ]);

        Setting::query()->updateOrCreate(
            ['key' => 'about_content_en'],
            ['value' => $validated['content_en'] ?? null, 'group' => 'about', 'description' => 'About Page (English)']
        );

        Setting::query()->updateOrCreate(
            ['key' => 'about_content_tr'],
            ['value' => $validated['content_tr'] ?? null, 'group' => 'about', 'description' => 'About Page (Turkish)']
        );

        return to_route('admin.about')->with('success', 'About page updated.');
    }
}
