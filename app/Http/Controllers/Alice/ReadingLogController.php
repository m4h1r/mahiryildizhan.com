<?php

namespace App\Http\Controllers\Alice;

use App\Http\Resources\Alice\ReadingLogResource;
use App\Models\ReadingLog;

class ReadingLogController extends AbstractSyncEntityController
{
    /** @var array<int, string> */
    protected array $sortable = ['book_title', 'author', 'read_on', 'created_at'];

    protected function modelClass(): string
    {
        return ReadingLog::class;
    }

    protected function resourceClass(): string
    {
        return ReadingLogResource::class;
    }

    protected function rules(bool $partial, ?int $id): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'book_title' => [$required, 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'pages' => [$required, 'integer', 'min:0'],
            'read_on' => [$required, 'date'],
            'note' => ['nullable', 'string', 'max:20000'],
        ];
    }
}
