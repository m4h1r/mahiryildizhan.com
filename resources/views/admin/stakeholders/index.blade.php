@extends('admin.layout', ['title' => 'Stakeholders', 'heading' => 'Stakeholders'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input class="form-input-admin sm:col-span-2 lg:col-span-1" name="q" placeholder="Search title, vkn, email..." value="{{ $filters['q'] ?? '' }}">

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
                    <button type="submit" class="admin-btn admin-btn-primary flex-1">Filter</button>
                    <a href="{{ route('admin.stakeholders.index') }}" class="admin-btn admin-btn-ghost flex-1">Reset</a>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">Stakeholder List</h2>
                <a href="{{ route('admin.stakeholders.create') }}" class="admin-btn admin-btn-primary">New Stakeholder</a>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="hidden sm:table-cell">VKN/TCKN</th>
                            <th>Title</th>
                            <th class="hidden md:table-cell">Type</th>
                            <th class="hidden md:table-cell">Status</th>
                            <th class="hidden lg:table-cell">Contact</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stakeholders as $stakeholder)
                            <tr>
                                <td class="hidden sm:table-cell">{{ $stakeholder->vkn_tckn }}</td>
                                <td class="font-medium">{{ $stakeholder->title ?: trim(($stakeholder->name ?? '').' '.($stakeholder->surname ?? '')) }}</td>
                                <td class="hidden md:table-cell">{{ $stakeholder->company_type }}</td>
                                <td class="hidden md:table-cell">{{ $stakeholder->status }}</td>
                                <td class="hidden lg:table-cell">{{ $stakeholder->email ?: '-' }}</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('admin.stakeholders.edit', $stakeholder) }}" class="admin-btn-sm admin-btn-ghost">Edit</a>

                                        <form method="POST" action="{{ route('admin.stakeholders.duplicate', $stakeholder) }}">
                                            @csrf
                                            <button type="submit" class="admin-btn-sm admin-btn-ghost">Duplicate</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.stakeholders.destroy', $stakeholder) }}" onsubmit="return confirm('Delete this stakeholder?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger">Delete</button>
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

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                {{ $stakeholders->links() }}
            </div>
        </section>
    </div>
@endsection
