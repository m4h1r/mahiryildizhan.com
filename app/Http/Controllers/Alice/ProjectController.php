<?php

namespace App\Http\Controllers\Alice;

use App\Http\Resources\Alice\ProjectResource;
use App\Models\Project;
use Illuminate\Validation\Rule;

class ProjectController extends AbstractSyncEntityController
{
    /** @var array<int, string> */
    protected array $sortable = ['name', 'status', 'due', 'idea_date', 'created_at'];

    protected function modelClass(): string
    {
        return Project::class;
    }

    protected function resourceClass(): string
    {
        return ProjectResource::class;
    }

    protected function rules(bool $partial, ?int $id): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'slug' => [$required, 'string', 'max:80', Rule::unique('projects', 'slug')->ignore($id)],
            'name' => [$required, 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['fikir', 'gelistirme', 'aktif', 'pasif', 'arsiv'])],
            'progress' => ['nullable', 'integer', 'between:0,100'],
            'note' => ['nullable', 'string', 'max:20000'],
            'due' => ['nullable', 'date'],
            'due_postponed' => ['nullable', 'boolean'],
            'idea_date' => ['nullable', 'date'],
            'local_path' => ['nullable', 'string', 'max:255'],
            'repo_url' => ['nullable', 'string', 'max:255'],
            'scope_ref' => ['nullable', 'string', 'max:255'],
        ];
    }
}
