<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StorePersonRequest;
use App\Http\Requests\Alice\UpdatePersonRequest;
use App\Http\Resources\Alice\PersonResource;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Person::with(['gender', 'bloodType', 'eyeColor', 'hairColor'])
            ->when($request->query('q'), function ($q, $search) {
                $q->where(fn ($sub) => $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%"));
            })
            ->when($request->query('gender_id'), fn ($q, $id) => $q->where('gender_id', $id))
            ->when($request->query('with_trashed'), fn ($q) => $q->withTrashed());

        $this->applySort($query, $request, ['name', 'surname', 'birthday', 'created_at']);

        return $this->paginate($query, $request, PersonResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $person = Person::with(['gender', 'bloodType', 'eyeColor', 'hairColor', 'father', 'mother', 'partner'])->find($id);

        return $person ? $this->success(new PersonResource($person)) : $this->notFound();
    }

    public function store(StorePersonRequest $request): JsonResponse
    {
        $person = Person::create($request->validated());
        $person->load(['gender', 'bloodType', 'eyeColor', 'hairColor']);

        return $this->success(new PersonResource($person), 201);
    }

    public function update(UpdatePersonRequest $request, int $id): JsonResponse
    {
        $person = Person::find($id);
        if (! $person) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $person);
        $person->update($request->validated());
        $person->load(['gender', 'bloodType', 'eyeColor', 'hairColor']);

        return $this->success(new PersonResource($person));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $person = Person::find($id);
        if (! $person) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $person);
        $person->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
