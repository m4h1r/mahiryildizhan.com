@extends('admin.layout', ['title' => 'Person Detail', 'heading' => 'Person Detail'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold">{{ $person->name }} {{ $person->surname }}</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $person->email ?: '-' }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.people.edit', $person) }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">{{ __('Edit') }}</a>
                    <a href="{{ route('admin.people.graph', $person) }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">{{ __('Graph') }}</a>
                    <a href="{{ route('admin.people.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">{{ __('Back') }}</a>
                </div>
            </div>

            <dl class="grid gap-3 text-sm md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Gender') }}</dt>
                    <dd class="mt-1">{{ optional($person->gender)->name ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Birthday') }}</dt>
                    <dd class="mt-1">{{ optional($person->birthday)->format('Y-m-d') ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Birth Place') }}</dt>
                    <dd class="mt-1">{{ $person->birth_place ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Zodiac') }}</dt>
                    <dd class="mt-1">{{ $person->zodiacName() ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Father') }}</dt>
                    <dd class="mt-1">{{ optional($person->father)->name ? $person->father->name.' '.$person->father->surname : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Mother') }}</dt>
                    <dd class="mt-1">{{ optional($person->mother)->name ? $person->mother->name.' '.$person->mother->surname : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Partner') }}</dt>
                    <dd class="mt-1">{{ optional($person->partner)->name ? $person->partner->name.' '.$person->partner->surname : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Mobile') }}</dt>
                    <dd class="mt-1">{{ $person->mobile ?: '-' }}</dd>
                </div>
            </dl>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold">{{ __('Interactions') }}</h3>
                <a href="{{ route('admin.interactions.create', ['person_id' => $person->id]) }}" class="rounded-md bg-gray-900 px-3 py-2 text-xs font-medium text-white dark:bg-white dark:text-gray-900">{{ __('New Interaction') }}</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">{{ __('Date') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ __('Type') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ __('Effect') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ __('Notes') }}</th>
                            <th class="px-4 py-3 text-right font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($interactions as $interaction)
                            <tr>
                                <td class="px-4 py-3">{{ optional($interaction->date)->format('Y-m-d') ?: '-' }}</td>
                                <td class="px-4 py-3">{{ optional($interaction->type)->name ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $interaction->effect ?: '-' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit((string) $interaction->notes, 100) ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.interactions.edit', $interaction) }}" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">{{ __('Edit') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No interactions yet.') }}</td>
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
