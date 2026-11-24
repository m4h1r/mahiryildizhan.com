<?php

namespace App\Http\Controllers\Alice;

use App\Http\Resources\Alice\WealthEntryResource;
use App\Models\WealthEntry;

class WealthEntryController extends AbstractSyncEntityController
{
    /** @var array<int, string> */
    protected array $sortable = ['kind', 'label', 'amount', 'as_of', 'created_at'];

    protected function modelClass(): string
    {
        return WealthEntry::class;
    }

    protected function resourceClass(): string
    {
        return WealthEntryResource::class;
    }

    protected function rules(bool $partial, ?int $id): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'kind' => [$required, 'string', 'max:255'],
            'label' => [$required, 'string', 'max:255'],
            'amount' => [$required, 'numeric'],
            'currency' => [$required, 'string', 'size:3'],
            'as_of' => [$required, 'date'],
            'note' => ['nullable', 'string', 'max:20000'],
        ];
    }
}
