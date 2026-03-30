<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Adage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdageController extends Controller
{
    public function index(Request $request): View
    {
        $query = Adage::query()->latest('id');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('owner', 'like', "%{$search}%")
                    ->orWhere('adage', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%")
                    ->orWhere('language', 'like', "%{$search}%");
            });
        }

        return view('admin.adages.index', [
            'title' => 'Adages',
            'heading' => 'Adages',
            'adages' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q']),
        ]);
    }

    public function create(): View
    {
        return view('admin.adages.create', [
            'title' => 'New Adage',
            'heading' => 'New Adage',
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
            'language' => ['nullable', 'string', 'max:255'],
        ]);
    }
}