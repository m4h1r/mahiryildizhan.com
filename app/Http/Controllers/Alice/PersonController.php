<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StorePersonRequest;
use App\Http\Requests\Alice\UpdatePersonRequest;
use App\Http\Resources\Alice\PersonResource;
use App\Models\Person;
use App\Support\Crm\CrmRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonController extends AliceController
{
    /** Relations a client may request with ?include=a,b */
    private const INCLUDES = ['channels' => 'channels', 'latest_assessment' => 'latestAssessment', 'faces' => 'faces'];

    public function index(Request $request): JsonResponse
    {
        $query = Person::with(['gender', 'bloodType', 'eyeColor', 'hairColor', ...$this->includes($request)])
            ->when($request->query('q'), function ($q, $search) {
                $q->where(fn ($sub) => $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%"));
            })
            ->when($request->query('gender_id'), fn ($q, $id) => $q->where('gender_id', $id))
            ->when($request->query('relationship_scope'), fn ($q, $scope) => $q->where('relationship_scope', $scope))
            ->when($request->query('alice_slug'), fn ($q, $slug) => $q->where('alice_slug', $slug))
            ->when($request->boolean('missing_assessment'), fn ($q) => $q->whereDoesntHave('assessments'))
            ->when($request->query('with_trashed'), fn ($q) => $q->withTrashed());

        $this->applySort($query, $request, ['name', 'surname', 'birthday', 'created_at']);

        return $this->paginate($query, $request, PersonResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $person = Person::with(['gender', 'bloodType', 'eyeColor', 'hairColor', ...$this->includes(request())])->find($id);

        return $person ? $this->success(new PersonResource($person)) : $this->notFound();
    }

    /**
     * The full person card in one call: person + channels + latest assessment + face metadata.
     */
    public function card(int $id): JsonResponse
    {
        $person = Person::with(['gender', 'bloodType', 'eyeColor', 'hairColor', 'channels', 'latestAssessment', 'faces'])->find($id);

        return $person ? $this->success(new PersonResource($person)) : $this->notFound();
    }

    public function store(StorePersonRequest $request): JsonResponse
    {
        $person = Person::create(CrmRules::cleanTags($request->validated()));
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
        $person->update(CrmRules::cleanTags($request->validated()));
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

    /**
     * @return array<int, string>
     */
    private function includes(Request $request): array
    {
        $asked = array_filter(array_map('trim', explode(',', (string) $request->query('include', ''))));

        return array_values(array_intersect_key(self::INCLUDES, array_flip($asked)));
    }
}
