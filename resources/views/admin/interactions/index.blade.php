@extends('admin.layout', ['title' => 'Interactions', 'heading' => 'Interactions'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="grid gap-3 md:grid-cols-4">
                <input class="form-input-admin" name="q" placeholder="Search notes or person..." value="{{ $filters['q'] ?? '' }}">

                <select name="person_id" class="form-input-admin">
                    <option value="">All People</option>
                    @foreach ($people as $person)
                        <option value="{{ $person->id }}" @selected((string) ($filters['person_id'] ?? '') === (string) $person->id)>
                            {{ $person->surname }}, {{ $person->name }}
                        </option>
                    @endforeach
                </select>

                <select name="interaction_type_id" class="form-input-admin">
                    <option value="">All Types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected((string) ($filters['interaction_type_id'] ?? '') === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Filter</button>
                    <a href="{{ route('admin.interactions.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Reset</a>
                </div>
            </form>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Interaction List</h2>
                <a href="{{ route('admin.interactions.create') }}" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">New Interaction</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Person</th>
                            <th class="px-4 py-3 text-left font-medium">Type</th>
                            <th class="px-4 py-3 text-left font-medium">Effect</th>
                            <th class="px-4 py-3 text-left font-medium">Notes</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($interactions as $interaction)
                            <tr>
                                <td class="px-4 py-3">{{ optional($interaction->date)->toDateString() }}</td>
                                <td class="px-4 py-3">{{ optional($interaction->person)->name }} {{ optional($interaction->person)->surname }}</td>
                                <td class="px-4 py-3">{{ optional($interaction->type)->name ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $interaction->effect ?: '-' }}</td>
                                <td class="px-4 py-3 max-w-sm text-xs">{{ \Illuminate\Support\Str::limit($interaction->notes, 100) ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.interactions.edit', $interaction) }}" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Edit</a>

                                        <form method="POST" action="{{ route('admin.interactions.duplicate', $interaction) }}">
                                            @csrf
                                            <button type="submit" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Duplicate</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.interactions.destroy', $interaction) }}" onsubmit="return confirm('Delete this interaction?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-700 dark:border-red-900 dark:text-red-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No interactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                {{ $interactions->links() }}
            </div>
        </section>
    </div>
@endsection
