@extends('admin.layout', ['title' => 'Activity Log', 'heading' => 'Activity Log'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <select name="action" class="form-input-admin">
                    <option value="">{{ __('All Actions') }}</option>
                    @foreach (['created', 'updated', 'deleted', 'login', 'logout', 'login_failed'] as $actionOption)
                        <option value="{{ $actionOption }}" @selected(($filters['action'] ?? '') === $actionOption)>{{ ucfirst(str_replace('_', ' ', $actionOption)) }}</option>
                    @endforeach
                </select>
                <select name="model_type" class="form-input-admin">
                    <option value="">{{ __('All Models') }}</option>
                    @foreach ($modelTypes as $modelType)
                        <option value="{{ $modelType }}" @selected(($filters['model_type'] ?? '') === $modelType)>{{ class_basename($modelType) }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2 sm:col-span-2 lg:col-span-2">
                    <button type="submit" class="admin-btn admin-btn-primary flex-1">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.activity-log.index') }}" class="admin-btn admin-btn-ghost flex-1">{{ __('Reset') }}</a>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">{{ __('Activity') }}</h2>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('When') }}</th>
                            <th>{{ __('Action') }}</th>
                            <th>{{ __('Model') }}</th>
                            <th class="hidden md:table-cell">{{ __('Actor') }}</th>
                            <th class="hidden lg:table-cell">{{ __('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    @php($badgeColor = match(true) {
                                        in_array($log->action, ['created', 'login'], true) => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                        in_array($log->action, ['deleted', 'login_failed'], true) => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                        default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                    })
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeColor }}">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                </td>
                                <td>{{ class_basename($log->model_type) }} @if ($log->model_id) #{{ $log->model_id }} @endif</td>
                                <td class="hidden md:table-cell">{{ $log->user?->name ?? __('System / Anonymous') }}</td>
                                <td class="hidden lg:table-cell">
                                    @if (! empty($log->changes))
                                        <details>
                                            <summary class="cursor-pointer text-xs text-gray-500 dark:text-gray-400">{{ __('View') }}</summary>
                                            <pre class="mt-1 max-w-md overflow-x-auto rounded bg-gray-50 p-2 text-[11px] dark:bg-gray-800">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </details>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No activity recorded yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">{{ $logs->links() }}</div>
        </section>
    </div>
@endsection
