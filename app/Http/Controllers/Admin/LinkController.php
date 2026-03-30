<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function index(Request $request): View
    {
        $query = Link::query()->latest('id');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('slug', 'like', "%{$search}%")
                    ->orWhere('original_name', 'like', "%{$search}%")
                    ->orWhere('file_path', 'like', "%{$search}%");
            });
        }

        return view('admin.links.index', [
            'title' => 'Links',
            'heading' => 'Links',
            'links' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q']),
        ]);
    }

    public function create(): View
    {
        return view('admin.links.create', [
            'title' => 'New Link',
            'heading' => 'New Link',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Link::query()->create($this->validatedPayload($request));

        return to_route('admin.links.index')->with('success', 'Link created.');
    }

    public function edit(Link $link): View
    {
        return view('admin.links.edit', [
            'title' => 'Edit Link',
            'heading' => 'Edit Link',
            'link' => $link,
        ]);
    }

    public function update(Request $request, Link $link): RedirectResponse
    {
        $link->update($this->validatedPayload($request, $link->id));

        return to_route('admin.links.index')->with('success', 'Link updated.');
    }

    public function destroy(Link $link): RedirectResponse
    {
        $link->delete();

        return to_route('admin.links.index')->with('success', 'Link deleted.');
    }

    private function validatedPayload(Request $request, ?int $ignoreId = null): array
    {
        $payload = $request->validate([
            'slug' => ['nullable', 'string', 'max:255', 'unique:links,slug,'.($ignoreId ?? 'NULL').',id'],
            'file_path' => ['required', 'string', 'max:255'],
            'original_name' => ['required', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'integer', 'min:0'],
            'expires_at' => ['nullable', 'date'],
        ]);

        if (empty($payload['slug'])) {
            $payload['slug'] = Str::slug($payload['original_name']) ?: Str::random(8);
        }

        return $payload;
    }
}