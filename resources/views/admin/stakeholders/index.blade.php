@extends('admin.layout', ['title' => 'Stakeholders', 'heading' => 'Stakeholders'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="grid gap-3 md:grid-cols-4">
                <input class="form-input-admin" name="q" placeholder="Search title, vkn, email..." value="{{ $filters['q'] ?? '' }}">

                <select name="status" class="form-input-admin">
                    <option value="">All Statuses</option>
                    @foreach (['Active', 'Passive'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>

                <select name="company_type" class="form-input-admin">
                    <option value="">All Types</option>
                    @foreach (['Company', 'Individual'] as $type)
                        <option value="{{ $type }}" @selected(($filters['company_type'] ?? '') === $type)>{{ $type }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Filter</button>
                    <a href="{{ route('admin.stakeholders.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Reset</a>
                </div>
            </form>
        </section>

        <section class="card-admin overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Stakeholder List</h2>
                <a href="{{ route('admin.stakeholders.create') }}" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">New Stakeholder</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">VKN/TCKN</th>
                            <th class="px-4 py-3 text-left font-medium">Title</th>
                            <th class="px-4 py-3 text-left font-medium">Type</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Contact</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($stakeholders as $stakeholder)
                            <tr>
                                <td class="px-4 py-3">{{ $stakeholder->vkn_tckn }}</td>
                                <td class="px-4 py-3">{{ $stakeholder->title ?: trim(($stakeholder->name ?? '').' '.($stakeholder->surname ?? '')) }}</td>
                                <td class="px-4 py-3">{{ $stakeholder->company_type }}</td>
                                <td class="px-4 py-3">{{ $stakeholder->status }}</td>
                                <td class="px-4 py-3">{{ $stakeholder->email ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.stakeholders.edit', $stakeholder) }}" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Edit</a>

                                        <form method="POST" action="{{ route('admin.stakeholders.duplicate', $stakeholder) }}">
                                            @csrf
                                            <button type="submit" class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium dark:border-gray-700">Duplicate</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.stakeholders.destroy', $stakeholder) }}" onsubmit="return confirm('Delete this stakeholder?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-700 dark:border-red-900 dark:text-red-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No stakeholders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                {{ $stakeholders->links() }}
            </div>
        </section>
    </div>
@endsection
