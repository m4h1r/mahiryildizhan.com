@extends('admin.layout', ['title' => 'Node Connections', 'heading' => 'Node Connections'])

@section('content')
    <section class="admin-table-shell">
        <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
            <h2 class="text-sm font-semibold">{{ __('Connection List') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.nodes.graph') }}" class="admin-btn admin-btn-ghost">{{ __('Graph View') }}</a>
                <a href="{{ route('admin.node-connections.create') }}" class="admin-btn admin-btn-primary">{{ __('New Connection') }}</a>
            </div>
        </div>

        <div class="overflow-x-auto overscroll-x-contain">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>{{ __('From') }}</th>
                        <th>{{ __('To') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($connections as $connection)
                        <tr>
                            <td>{{ optional($connection->fromNode)->name ?: '-' }}</td>
                            <td>{{ optional($connection->toNode)->name ?: '-' }}</td>
                            <td>
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('admin.node-connections.edit', $connection) }}" class="admin-btn-sm admin-btn-ghost">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.node-connections.destroy', $connection) }}" onsubmit="return confirm('{{ __('Delete this connection?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn-sm admin-btn-danger">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No connections found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
            {{ $connections->links() }}
        </div>
    </section>
@endsection
