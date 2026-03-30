<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePersonRequest;
use App\Models\BloodType;
use App\Models\EyeColor;
use App\Models\Gender;
use App\Models\HairColor;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonController extends Controller
{
    public function index(Request $request): View
    {
        $allowedSorts = ['id', 'name', 'surname', 'second_surname', 'birthday', 'deathday', 'created_at'];
        $sort = (string) $request->string('sort', 'id');
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'id';
        $dir = strtolower((string) $request->string('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = Person::query()
            ->with(['gender', 'father', 'mother', 'partner'])
            ->orderBy($sort, $dir)
            ->orderBy('id', 'asc');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('second_surname', 'like', "%{$search}%");
            });
        }

        if ($genderId = $request->integer('gender_id')) {
            $query->where('gender_id', $genderId);
        }

        if ($alive = $request->string('alive')->toString()) {
            if ($alive === '1') {
                $query->whereNull('deathday');
            }

            if ($alive === '0') {
                $query->whereNotNull('deathday');
            }
        }

        return view('admin.people.index', [
            'title' => 'People',
            'heading' => 'People',
            'people' => $query->paginate(20)->withQueryString(),
            'genders' => Gender::query()->orderBy('name')->get(),
            'filters' => $request->only(['q', 'gender_id', 'alive', 'sort', 'dir']),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q'));

        if ($search === '') {
            return response()->json([]);
        }

        $people = Person::query()
            ->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('second_surname', 'like', "%{$search}%");
            })
            ->orderBy('surname')
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'surname', 'birthday']);

        return response()->json($people);
    }

    public function create(): View
    {
        return view('admin.people.create', [
            'title' => 'New Person',
            'heading' => 'New Person',
            ...$this->formData(),
        ]);
    }

    public function show(Person $person): View
    {
        $person->load(['gender', 'father', 'mother', 'partner', 'eyeColor', 'bloodType', 'hairColor']);

        $interactions = $person->interactions()
            ->with('type')
            ->latest('date')
            ->latest('id')
            ->paginate(15);

        return view('admin.people.show', [
            'title' => 'Person Detail',
            'heading' => 'Person Detail',
            'person' => $person,
            'interactions' => $interactions,
        ]);
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        Person::query()->create($request->validated());

        return to_route('admin.people.index')->with('success', __('Person created.'));
    }

    public function edit(Person $person): View
    {
        return view('admin.people.edit', [
            'title' => 'Edit Person',
            'heading' => 'Edit Person',
            'person' => $person,
            ...$this->formData($person->id),
        ]);
    }

    public function update(StorePersonRequest $request, Person $person): RedirectResponse
    {
        $person->update($request->validated());

        return to_route('admin.people.index')->with('success', __('Person updated.'));
    }

    public function destroy(Person $person): RedirectResponse
    {
        $person->delete();

        return to_route('admin.people.index')->with('success', __('Person deleted.'));
    }

    public function showTree(Person $person): View
    {
        $person->load([
            'gender',
            'father.gender', 'father.father.gender', 'father.mother.gender',
            'mother.gender', 'mother.father.gender', 'mother.mother.gender',
            'partner.gender',
        ]);

        $nodes = [];
        $edges = [];

        $genderGroup = static function (?Person $p): string {
            $name = strtolower((string) optional($p?->gender)->name);
            if (in_array($name, ['male', 'erkek'], true)) {
                return 'male';
            }
            if (in_array($name, ['female', 'kadın', 'kadin', 'woman'], true)) {
                return 'female';
            }

            return 'other';
        };

        // Level 0 — center person
        $this->addPersonNodeWithLevel($nodes, $person, $genderGroup($person), 0, true);

        // Level 1 — father + level-2 paternal grandparents
        if ($person->father) {
            $this->addPersonNodeWithLevel($nodes, $person->father, $genderGroup($person->father), 1);
            $edges[] = ['from' => $person->father->id, 'to' => $person->id, 'label' => 'father', 'arrows' => 'to'];

            if ($person->father->father) {
                $this->addPersonNodeWithLevel($nodes, $person->father->father, $genderGroup($person->father->father), 2);
                $edges[] = ['from' => $person->father->father->id, 'to' => $person->father->id, 'label' => 'father', 'arrows' => 'to'];
            }

            if ($person->father->mother) {
                $this->addPersonNodeWithLevel($nodes, $person->father->mother, $genderGroup($person->father->mother), 2);
                $edges[] = ['from' => $person->father->mother->id, 'to' => $person->father->id, 'label' => 'mother', 'arrows' => 'to'];
            }
        }

        // Level 1 — mother + level-2 maternal grandparents
        if ($person->mother) {
            $this->addPersonNodeWithLevel($nodes, $person->mother, $genderGroup($person->mother), 1);
            $edges[] = ['from' => $person->mother->id, 'to' => $person->id, 'label' => 'mother', 'arrows' => 'to'];

            if ($person->mother->father) {
                $this->addPersonNodeWithLevel($nodes, $person->mother->father, $genderGroup($person->mother->father), 2);
                $edges[] = ['from' => $person->mother->father->id, 'to' => $person->mother->id, 'label' => 'father', 'arrows' => 'to'];
            }

            if ($person->mother->mother) {
                $this->addPersonNodeWithLevel($nodes, $person->mother->mother, $genderGroup($person->mother->mother), 2);
                $edges[] = ['from' => $person->mother->mother->id, 'to' => $person->mother->id, 'label' => 'mother', 'arrows' => 'to'];
            }
        }

        // Level 0 — partner
        if ($person->partner) {
            $this->addPersonNodeWithLevel($nodes, $person->partner, 'partner', 0);
            $edges[] = ['from' => $person->id, 'to' => $person->partner->id, 'label' => 'partner', 'arrows' => ''];
        }

        // Level 0 — siblings (share at least one parent)
        if ($person->father_id || $person->mother_id) {
            $siblings = Person::with('gender')
                ->where('id', '!=', $person->id)
                ->where(function ($q) use ($person) {
                    if ($person->father_id) {
                        $q->orWhere('father_id', $person->father_id);
                    }
                    if ($person->mother_id) {
                        $q->orWhere('mother_id', $person->mother_id);
                    }
                })
                ->get();

            foreach ($siblings as $sibling) {
                if (isset($nodes[$sibling->id])) {
                    continue;
                }

                $this->addPersonNodeWithLevel($nodes, $sibling, 'sibling', 0);

                if ($person->father_id && $sibling->father_id === $person->father_id && isset($nodes[$person->father_id])) {
                    $edges[] = ['from' => $person->father_id, 'to' => $sibling->id, 'label' => 'child', 'arrows' => 'to'];
                } elseif ($person->mother_id && $sibling->mother_id === $person->mother_id && isset($nodes[$person->mother_id])) {
                    $edges[] = ['from' => $person->mother_id, 'to' => $sibling->id, 'label' => 'child', 'arrows' => 'to'];
                } else {
                    $edges[] = ['from' => $person->id, 'to' => $sibling->id, 'label' => 'sibling', 'arrows' => ''];
                }
            }
        }

        // Level -1 — children
        foreach ($person->allChildrenQuery()->with('gender')->get() as $child) {

            if (isset($nodes[$child->id])) {
                continue;
            }

            $this->addPersonNodeWithLevel($nodes, $child, $genderGroup($child), -1);
            $edges[] = ['from' => $person->id, 'to' => $child->id, 'label' => 'child', 'arrows' => 'to'];
        }

        return view('admin.people.tree', [
            'title' => 'Family Tree',
            'heading' => 'Family Tree - '.$person->name.' '.$person->surname,
            'person' => $person,
            'graphData' => [
                'nodes' => array_values($nodes),
                'edges' => $edges,
            ],
        ]);
    }

    public function showGraph(Person $person): View
    {
        $person->load([
            'bloodType',
            'father.bloodType',
            'father.father.bloodType',
            'father.mother.bloodType',
            'mother.bloodType',
            'mother.father.bloodType',
            'mother.mother.bloodType',
            'partner.bloodType',
        ]);

        $siblings = collect();

        if ($person->father_id || $person->mother_id) {
            $siblings = Person::query()
                ->with('bloodType')
                ->where('id', '!=', $person->id)
                ->where(function ($query) use ($person): void {
                    if ($person->father_id) {
                        $query->orWhere('father_id', $person->father_id);
                    }

                    if ($person->mother_id) {
                        $query->orWhere('mother_id', $person->mother_id);
                    }
                })
                ->orderBy('birthday')
                ->orderBy('id')
                ->get();
        }

        return view('admin.people.graph', [
            'title' => 'Family Graph',
            'heading' => 'Family Graph',
            'person' => $person,
            'siblings' => $siblings,
        ]);
    }

    private function formData(?int $excludeId = null): array
    {
        return [
            'genders' => Gender::query()->orderBy('name')->get(),
            'eyeColors' => EyeColor::query()->orderBy('name')->get(),
            'bloodTypes' => BloodType::query()->orderBy('name')->get(),
            'hairColors' => HairColor::query()->orderBy('name')->get(),
            'peopleOptions' => Person::query()
                ->when($excludeId !== null, fn ($query) => $query->where('id', '!=', $excludeId))
                ->orderBy('surname')
                ->orderBy('name')
                ->get(['id', 'name', 'surname', 'birthday']),
        ];
    }

    private function addPersonNode(array &$nodes, Person $person, string $color): void
    {
        $nodes[$person->id] = [
            'id' => $person->id,
            'label' => trim($person->name.' '.$person->surname),
            'title' => trim($person->name.' '.$person->surname),
            'shape' => 'box',
            'color' => $color,
            'font' => ['color' => '#ffffff'],
        ];
    }

    private function addPersonNodeWithLevel(array &$nodes, Person $person, string $group, int $level, bool $isCenter = false): void
    {
        $birthday = $person->birthday ? $person->birthday->format('Y-m-d') : null;
        $nodes[$person->id] = [
            'id' => $person->id,
            'label' => trim($person->name.' '.$person->surname),
            'title' => trim($person->name.' '.$person->surname).($birthday ? "\nBorn: {$birthday}" : ''),
            'level' => $level,
            'group' => $group,
            'borderWidth' => $isCenter ? 3 : 1,
        ];
    }
}
