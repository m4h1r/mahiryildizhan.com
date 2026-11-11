<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\PersonAssessmentRequest;
use App\Http\Resources\Alice\PersonAssessmentResource;
use App\Models\Person;
use App\Models\PersonAssessment;
use App\Support\Crm\CrmRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Personality / relationship assessments. Every POST is a new snapshot; the
 * newest by assessed_at is what the person card shows.
 */
class PersonAssessmentController extends AliceController
{
    public function index(Request $request, int $person): JsonResponse
    {
        if (! Person::whereKey($person)->exists()) {
            return $this->notFound();
        }

        $query = PersonAssessment::where('person_id', $person)->orderByDesc('assessed_at')->orderByDesc('id');

        return $this->paginate($query, $request, PersonAssessmentResource::class);
    }

    public function show(int $person, int $assessment): JsonResponse
    {
        $model = PersonAssessment::where('person_id', $person)->find($assessment);

        return $model ? $this->success(new PersonAssessmentResource($model)) : $this->notFound();
    }

    public function store(PersonAssessmentRequest $request, int $person): JsonResponse
    {
        $this->auditTable('person_assessments');
        $model = Person::find($person);
        if (! $model) {
            return $this->notFound();
        }

        $data = CrmRules::cleanAssessment($request->validated());
        $data['source'] ??= 'alice';

        $assessment = $model->assessments()->create($data);

        return $this->success(new PersonAssessmentResource($assessment), 201);
    }

    public function update(PersonAssessmentRequest $request, int $person, int $assessment): JsonResponse
    {
        $this->auditTable('person_assessments');
        $model = PersonAssessment::where('person_id', $person)->find($assessment);
        if (! $model) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $model);
        $model->update(CrmRules::cleanAssessment($request->validated()));

        return $this->success(new PersonAssessmentResource($model));
    }

    public function destroy(Request $request, int $person, int $assessment): JsonResponse
    {
        $this->auditTable('person_assessments');
        $model = PersonAssessment::where('person_id', $person)->find($assessment);
        if (! $model) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $model);
        $model->delete();

        return response()->json(['data' => ['id' => $assessment, 'deleted' => true]]);
    }
}
