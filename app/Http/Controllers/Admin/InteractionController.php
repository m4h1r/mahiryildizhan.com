<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInteractionRequest;
use App\Models\Interaction;
use App\Models\InteractionType;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InteractionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Interaction::query()
            ->with(['person', 'type'])
            ->latest('date')
            ->latest('id');

        if ($personId = $request->integer('person_id')) {
            $query->where('person_id', $personId);
        }

        if ($typeId = $request->integer('interaction_type_id')) {
            $query->where('interaction_type_id', $typeId);
        }

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('effect', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('person', function ($personQuery) use ($search): void {
                        $personQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('surname', 'like', "%{$search}%");
                    });
            });
        }

        return view('admin.interactions.index', [
            'title' => 'Interactions',
            'heading' => 'Interactions',
            'interactions' => $query->paginate(20)->withQueryString(),
            'people' => Person::query()->orderBy('surname')->orderBy('name')->get(['id', 'name', 'surname']),
            'types' => InteractionType::query()->orderBy('name')->get(),
            'filters' => $request->only(['person_id', 'interaction_type_id', 'q']),
        ]);
    }

    public function womenInCircle(): View
    {
        $interactions = Interaction::query()
            ->with('person')
            ->where('interaction_type_id', 5)
            ->whereHas('person')
            ->latest('date')
            ->latest('id')
            ->get();

        $people = $interactions
            ->unique('person_id')
            ->map(fn (Interaction $interaction) => $interaction->person)
            ->values();

        $candidatePersonIds = Interaction::query()
            ->where('notes', 'like', '%#wiccandidate%')
            ->whereHas('person')
            ->pluck('person_id')
            ->unique();

        $candidates = Person::query()
            ->whereIn('id', $candidatePersonIds)
            ->get()
            ->map(function (Person $person) {
                $person->effectScore = (int) $person->interactions()->sum('effect');

                return $person;
            })
            ->sortByDesc('effectScore')
            ->values();

        return view('admin.interactions.women-in-circle', [
            'title' => 'Women In Circle',
            'heading' => 'Women In Circle',
            'people' => $people,
            'totalInteractions' => $interactions->count(),
            'centerPerson' => Person::find(1),
            'candidates' => $candidates,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.interactions.create', [
            'title' => 'New Interaction',
            'heading' => 'New Interaction',
            'defaultPersonId' => $request->integer('person_id') ?: null,
            ...$this->formData(),
        ]);
    }

    public function store(StoreInteractionRequest $request): RedirectResponse
    {
        Interaction::query()->create($request->validated());

        return to_route('admin.interactions.index')->with('success', 'Interaction created.');
    }

    public function edit(Interaction $interaction): View
    {
        return view('admin.interactions.edit', [
            'title' => 'Edit Interaction',
            'heading' => 'Edit Interaction',
            'interaction' => $interaction,
            ...$this->formData(),
        ]);
    }

    public function update(StoreInteractionRequest $request, Interaction $interaction): RedirectResponse
    {
        $interaction->update($request->validated());

        return to_route('admin.interactions.index')->with('success', 'Interaction updated.');
    }

    public function destroy(Interaction $interaction): RedirectResponse
    {
        $interaction->delete();

        return to_route('admin.interactions.index')->with('success', 'Interaction deleted.');
    }

    public function duplicate(Interaction $interaction): RedirectResponse
    {
        $copy = $interaction->replicate();
        $copy->date = now()->toDateString();
        $copy->save();

        return to_route('admin.interactions.edit', $copy)->with('success', 'Interaction duplicated.');
    }

    private function formData(): array
    {
        return [
            'people' => Person::query()->orderBy('surname')->orderBy('name')->get(['id', 'name', 'surname']),
            'types' => InteractionType::query()->orderBy('name')->get(),
        ];
    }
}
