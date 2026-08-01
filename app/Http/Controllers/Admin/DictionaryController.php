<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodType;
use App\Models\Currency;
use App\Models\ExpenseType;
use App\Models\EyeColor;
use App\Models\Gender;
use App\Models\HairColor;
use App\Models\IncomeSource;
use App\Models\IncomeType;
use App\Models\InteractionType;
use App\Models\PostCategory;
use App\Models\PostLanguage;
use App\Models\Sector;
use App\Models\TaxOffice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DictionaryController extends Controller
{
    private const DEFINITIONS = [
        'genders' => [
            'label' => 'Genders',
            'model' => Gender::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
                'slug' => ['label' => 'Slug', 'required' => false, 'max' => 255, 'unique' => true, 'auto_from' => 'name'],
            ],
        ],
        'eye_colors' => [
            'label' => 'Eye Colors',
            'model' => EyeColor::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
                'slug' => ['label' => 'Slug', 'required' => false, 'max' => 255, 'unique' => true, 'auto_from' => 'name'],
            ],
        ],
        'blood_types' => [
            'label' => 'Blood Types',
            'model' => BloodType::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
            ],
        ],
        'hair_colors' => [
            'label' => 'Hair Colors',
            'model' => HairColor::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
                'slug' => ['label' => 'Slug', 'required' => false, 'max' => 255, 'unique' => true, 'auto_from' => 'name'],
            ],
        ],
        'post_categories' => [
            'label' => 'Post Categories',
            'model' => PostCategory::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
                'slug' => ['label' => 'Slug', 'required' => false, 'max' => 255, 'unique' => true, 'auto_from' => 'name'],
                'description' => ['label' => 'Description', 'required' => false, 'type' => 'textarea'],
            ],
        ],
        'post_languages' => [
            'label' => 'Post Languages',
            'model' => PostLanguage::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
                'code' => ['label' => 'Code', 'required' => true, 'max' => 8, 'unique' => true, 'uppercase' => true],
            ],
        ],
        'income_sources' => [
            'label' => 'Income Sources',
            'model' => IncomeSource::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
            ],
        ],
        'income_types' => [
            'label' => 'Income Types',
            'model' => IncomeType::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
            ],
        ],
        'currencies' => [
            'label' => 'Currencies',
            'model' => Currency::class,
            'order_by' => 'code',
            'fields' => [
                'code' => ['label' => 'Code', 'required' => true, 'max' => 3, 'unique' => true, 'uppercase' => true],
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255],
                'symbol' => ['label' => 'Symbol', 'required' => false, 'max' => 8],
            ],
        ],
        'expense_types' => [
            'label' => 'Expense Types',
            'model' => ExpenseType::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
                'government_acceptance_percentage' => [
                    'label' => 'Government Acceptance (%)',
                    'required' => true,
                    'type' => 'number',
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                ],
            ],
        ],
        'interaction_types' => [
            'label' => 'Interaction Types',
            'model' => InteractionType::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
            ],
        ],
        'tax_offices' => [
            'label' => 'Tax Offices',
            'model' => TaxOffice::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
            ],
        ],
        'sectors' => [
            'label' => 'Sectors',
            'model' => Sector::class,
            'order_by' => 'name',
            'fields' => [
                'name' => ['label' => 'Name', 'required' => true, 'max' => 255, 'unique' => true],
            ],
        ],
    ];

    public function index(string $table): View
    {
        $definition = $this->definition($table);
        $records = $this->query($definition)->get();

        return view('admin.dictionaries.index', [
            'title' => $definition['label'],
            'heading' => $definition['label'],
            'table' => $table,
            'definition' => $definition,
            'records' => $records,
        ]);
    }

    public function store(Request $request, string $table): RedirectResponse
    {
        $definition = $this->definition($table);
        $payload = $this->validatedPayload($request, $table, $definition);

        $this->newModel($definition)->create($payload);

        return to_route('admin.dictionaries.index', $table)->with('success', $definition['label'].' entry created.');
    }

    public function update(Request $request, string $table, int $record): RedirectResponse
    {
        $definition = $this->definition($table);
        $model = $this->query($definition)->findOrFail($record);
        $payload = $this->validatedPayload($request, $table, $definition, $model->getKey());

        $model->update($payload);

        return to_route('admin.dictionaries.index', $table)->with('success', $definition['label'].' entry updated.');
    }

    public function destroy(string $table, int $record): RedirectResponse
    {
        $definition = $this->definition($table);
        $model = $this->query($definition)->findOrFail($record);
        $model->delete();

        return to_route('admin.dictionaries.index', $table)->with('success', $definition['label'].' entry deleted.');
    }

    public static function navigation(): array
    {
        return collect(self::DEFINITIONS)
            ->map(fn (array $definition, string $table) => [
                'table' => $table,
                'label' => $definition['label'],
            ])
            ->values()
            ->all();
    }

    private function definition(string $table): array
    {
        abort_unless(array_key_exists($table, self::DEFINITIONS), 404);

        return self::DEFINITIONS[$table];
    }

    private function query(array $definition): Builder
    {
        return $this->newModel($definition)
            ->newQuery()
            ->orderBy($definition['order_by']);
    }

    private function newModel(array $definition): Model
    {
        $modelClass = $definition['model'];

        return new $modelClass;
    }

    private function validatedPayload(Request $request, string $table, array $definition, ?int $ignoreId = null): array
    {
        $rules = [];

        foreach ($definition['fields'] as $field => $meta) {
            $fieldRules = [$meta['required'] ? 'required' : 'nullable'];

            if (($meta['type'] ?? 'text') === 'number') {
                $fieldRules[] = 'numeric';
            } else {
                $fieldRules[] = 'string';
            }

            if (isset($meta['max'])) {
                $fieldRules[] = (($meta['type'] ?? 'text') === 'number' ? 'lte:' : 'max:').$meta['max'];
            }

            if (isset($meta['min'])) {
                $fieldRules[] = (($meta['type'] ?? 'text') === 'number' ? 'gte:' : 'min:').$meta['min'];
            }

            if (! empty($meta['unique'])) {
                $fieldRules[] = Rule::unique($table, $field)->ignore($ignoreId);
            }

            $rules[$field] = $fieldRules;
        }

        $payload = $request->validate($rules);

        foreach ($definition['fields'] as $field => $meta) {
            $value = $payload[$field] ?? null;

            if ($value === '') {
                $value = null;
            }

            if ($value === null && ! empty($meta['auto_from'])) {
                $source = $payload[$meta['auto_from']] ?? null;
                $value = $source ? Str::slug($source) : null;
            }

            if ($value !== null && ! empty($meta['uppercase'])) {
                $value = Str::upper($value);
            }

            $payload[$field] = $value;
        }

        return $payload;
    }
}
