<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Adage;
use App\Models\PostLanguage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdageController extends Controller
{
    public function index(Request $request): View
    {
        $query = Adage::query()->with('language')->latest('id');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('owner', 'like', "%{$search}%")
                    ->orWhere('adage', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%");
            });
        }

        if ($languageId = $request->integer('language_id')) {
            $query->where('language_id', $languageId);
        }

        return view('admin.adages.index', [
            'title' => 'Adages',
            'heading' => 'Adages',
            'adages' => $query->paginate(20)->withQueryString(),
            'languages' => PostLanguage::query()->orderBy('name')->get(),
            'filters' => $request->only(['q', 'language_id']),
        ]);
    }

    public function create(): View
    {
        return view('admin.adages.create', [
            'title' => 'New Adage',
            'heading' => 'New Adage',
            'languages' => PostLanguage::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Adage::query()->create($this->validatedPayload($request));

        return to_route('admin.adages.index')->with('success', 'Adage created.');
    }

    public function edit(Adage $adage): View
    {
        return view('admin.adages.edit', [
            'title' => 'Edit Adage',
            'heading' => 'Edit Adage',
            'adage' => $adage,
            'languages' => PostLanguage::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Adage $adage): RedirectResponse
    {
        $adage->update($this->validatedPayload($request));

        return to_route('admin.adages.index')->with('success', 'Adage updated.');
    }

    public function destroy(Adage $adage): RedirectResponse
    {
        $adage->delete();

        return to_route('admin.adages.index')->with('success', 'Adage deleted.');
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'owner' => ['required', 'string', 'max:255'],
            'adage' => ['required', 'string'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'language_id' => ['nullable', 'integer', 'exists:post_languages,id'],
        ]);
    }
}
