<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\PersonChannelRequest;
use App\Http\Resources\Alice\PersonChannelResource;
use App\Models\Person;
use App\Models\PersonChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonChannelController extends AliceController
{
    public function index(int $person): JsonResponse
    {
        $model = Person::find($person);

        return $model ? $this->success(PersonChannelResource::collection($model->channels)) : $this->notFound();
    }

    public function store(PersonChannelRequest $request, int $person): JsonResponse
    {
        $this->auditTable('person_channels');
        $model = Person::find($person);
        if (! $model) {
            return $this->notFound();
        }

        $channel = $model->channels()->create($request->validated());

        return $this->success(new PersonChannelResource($channel), 201);
    }

    public function update(PersonChannelRequest $request, int $person, int $channel): JsonResponse
    {
        $this->auditTable('person_channels');
        $model = PersonChannel::where('person_id', $person)->find($channel);
        if (! $model) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $model);
        $model->update($request->validated());

        return $this->success(new PersonChannelResource($model));
    }

    public function destroy(Request $request, int $person, int $channel): JsonResponse
    {
        $this->auditTable('person_channels');
        $model = PersonChannel::where('person_id', $person)->find($channel);
        if (! $model) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $model);
        $model->delete();

        return response()->json(['data' => ['id' => $channel, 'deleted' => true]]);
    }
}
