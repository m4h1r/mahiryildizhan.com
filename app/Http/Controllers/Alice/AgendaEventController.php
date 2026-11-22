<?php

namespace App\Http\Controllers\Alice;

use App\Http\Resources\Alice\AgendaEventResource;
use App\Models\AgendaEvent;
use Illuminate\Validation\Rule;

class AgendaEventController extends AbstractSyncEntityController
{
    /** @var array<int, string> */
    protected array $sortable = ['title', 'starts_at', 'created_at'];

    protected function modelClass(): string
    {
        return AgendaEvent::class;
    }

    protected function resourceClass(): string
    {
        return AgendaEventResource::class;
    }

    protected function rules(bool $partial, ?int $id): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'title' => [$required, 'string', 'max:255'],
            'starts_at' => [$required, 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'all_day' => ['nullable', 'boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:20000'],
            'source_ref' => ['nullable', 'string', 'max:255', Rule::unique('agenda_events', 'source_ref')->ignore($id)],
        ];
    }
}
