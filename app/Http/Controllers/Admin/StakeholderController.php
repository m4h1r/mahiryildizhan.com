<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStakeholderRequest;
use App\Models\Sector;
use App\Models\Stakeholder;
use App\Models\TaxOffice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StakeholderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Stakeholder::query()->with('creator');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('vkn_tckn', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($companyType = $request->string('company_type')->toString()) {
            $query->where('company_type', $companyType);
        }

        $stakeholders = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.stakeholders.index', [
            'title' => 'Stakeholders',
            'heading' => 'Stakeholders',
            'stakeholders' => $stakeholders,
            'filters' => $request->only(['q', 'status', 'company_type']),
        ]);
    }

    public function create(): View
    {
        return view('admin.stakeholders.create', [
            'title' => 'New Stakeholder',
            'heading' => 'New Stakeholder',
            'taxOffices' => TaxOffice::query()->orderBy('name')->get(),
            'sectors' => Sector::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreStakeholderRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['created_by'] = $request->user()?->id;

        Stakeholder::query()->create($payload);

        return to_route('admin.stakeholders.index')->with('success', 'Stakeholder created.');
    }

    public function edit(Stakeholder $stakeholder): View
    {
        return view('admin.stakeholders.edit', [
            'title' => 'Edit Stakeholder',
            'heading' => 'Edit Stakeholder',
            'stakeholder' => $stakeholder,
            'taxOffices' => TaxOffice::query()->orderBy('name')->get(),
            'sectors' => Sector::query()->orderBy('name')->get(),
        ]);
    }

    public function update(StoreStakeholderRequest $request, Stakeholder $stakeholder): RedirectResponse
    {
        $stakeholder->update($request->validated());

        return to_route('admin.stakeholders.index')->with('success', 'Stakeholder updated.');
    }

    public function destroy(Stakeholder $stakeholder): RedirectResponse
    {
        $stakeholder->delete();

        return to_route('admin.stakeholders.index')->with('success', 'Stakeholder deleted.');
    }

    public function duplicate(Stakeholder $stakeholder): RedirectResponse
    {
        $copy = $stakeholder->replicate(['vkn_tckn']);
        $copy->vkn_tckn = $stakeholder->vkn_tckn.'-copy-'.now()->format('His');
        $copy->created_by = request()->user()?->id;
        $copy->save();

        return to_route('admin.stakeholders.edit', $copy)->with('success', 'Stakeholder duplicated.');
    }

    public function lookup(Request $request): JsonResponse
    {
        $vkn = trim((string) ($request->query('vkn') ?: $request->query('q')));

        if ($vkn === '') {
            return response()->json([
                'found' => false,
                'message' => 'A VKN/TCKN value is required.',
            ], 422);
        }

        $stakeholder = Stakeholder::query()
            ->where('vkn_tckn', $vkn)
            ->first();

        if (! $stakeholder) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'data' => [
                'id' => $stakeholder->id,
                'title' => $stakeholder->title ?: trim(($stakeholder->name ?? '').' '.($stakeholder->surname ?? '')),
                'vkn_tckn' => $stakeholder->vkn_tckn,
                'company_type' => $stakeholder->company_type,
                'status' => $stakeholder->status,
            ],
        ]);
    }

    public function quickStore(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'vkn_tckn' => ['required', 'string', 'max:64', 'unique:stakeholders,vkn_tckn'],
            'title' => ['required_without:name', 'nullable', 'string', 'max:255'],
            'name' => ['required_without:title', 'nullable', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'company_type' => ['nullable', 'in:Company,Individual'],
            'status' => ['nullable', 'in:Active,Passive'],
        ]);

        $stakeholder = Stakeholder::query()->create([
            'vkn_tckn' => $payload['vkn_tckn'],
            'title' => $payload['title'] ?? null,
            'name' => $payload['name'] ?? null,
            'surname' => $payload['surname'] ?? null,
            'country' => 'TR',
            'company_type' => $payload['company_type'] ?? 'Company',
            'status' => $payload['status'] ?? 'Active',
            'created_by' => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $stakeholder->id,
                'title' => $stakeholder->title ?: trim(($stakeholder->name ?? '').' '.($stakeholder->surname ?? '')),
                'vkn_tckn' => $stakeholder->vkn_tckn,
            ],
        ], 201);
    }
}
