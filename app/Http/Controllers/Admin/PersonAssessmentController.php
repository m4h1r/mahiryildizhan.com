<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\PersonAssessment;
use App\Support\Crm\CrmRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manual personality / relationship assessments on the person card.
 * Each save of "new assessment" adds a snapshot; editing changes one in place.
 */
class PersonAssessmentController extends Controller
{
    public function create(Person $person): View
    {
        // Start from the latest snapshot so a re-evaluation only edits what changed.
        $base = $person->latestAssessment;
        $draft = $base ? $base->replicate(['created_by']) : new PersonAssessment;
        $draft->assessed_at = now();
        $draft->source = 'manual';

        return view('admin.people.assessments.form', [
            'title' => 'New assessment',
            'heading' => 'New assessment',
            'person' => $person,
            'assessment' => $draft,
            'isNew' => true,
        ]);
    }

    public function store(Request $request, Person $person): RedirectResponse
    {
        $this->linesToApproach($request);
        $data = CrmRules::cleanAssessment($request->validate(CrmRules::assessment()));
        $data['source'] ??= 'manual';

        $assessment = $person->assessments()->create($data);

        return to_route('admin.people.show', [$person, 'tab' => 'analysis', 'assessment' => $assessment->id])
            ->with('success', __('Assessment saved.'));
    }

    public function edit(Person $person, PersonAssessment $assessment): View
    {
        return view('admin.people.assessments.form', [
            'title' => 'Edit assessment',
            'heading' => 'Edit assessment',
            'person' => $person,
            'assessment' => $assessment,
            'isNew' => false,
        ]);
    }

    public function update(Request $request, Person $person, PersonAssessment $assessment): RedirectResponse
    {
        $this->linesToApproach($request);
        $data = CrmRules::cleanAssessment($request->validate(CrmRules::assessment()));
        $assessment->update($data);

        return to_route('admin.people.show', [$person, 'tab' => 'analysis', 'assessment' => $assessment->id])
            ->with('success', __('Assessment updated.'));
    }

    public function destroy(Person $person, PersonAssessment $assessment): RedirectResponse
    {
        $assessment->delete();

        return to_route('admin.people.show', [$person, 'tab' => 'analysis'])
            ->with('success', __('Assessment deleted.'));
    }

    /**
     * The admin form edits approach lists as one item per line.
     */
    private function linesToApproach(Request $request): void
    {
        $text = (array) $request->input('approach_text', []);
        $approach = [];
        foreach (array_keys(config('crm.approach_lists')) as $key) {
            $approach[$key] = preg_split('/\r\n|\r|\n/', (string) ($text[$key] ?? '')) ?: [];
        }
        $request->merge(['approach' => $approach]);
    }
}
