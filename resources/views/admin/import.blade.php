@extends('admin.layout')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[420px_minmax(0,1fr)]">
        <section class="card-admin p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('CSV Import Pipeline') }}</p>
            <h2 class="mt-1 text-xl font-semibold">{{ __('Run Import') }}</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                {{ __('Place CSV files in') }} <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-xs dark:bg-gray-800">database/csv/{table}.csv</code> {{ __('locally.') }}
                {{ __('Production reads from') }} <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-xs dark:bg-gray-800">storage/app/import/{table}.csv</code>.
            </p>

            {{-- Import All button --}}
            <form method="POST" action="{{ route('admin.import.run') }}" class="mt-5" data-confirm="{{ __('Import ALL tables in dependency order? This cannot be undone.') }}">
                @csrf
                <input type="hidden" name="import_all" value="1">
                <button
                    type="submit"
                    class="w-full rounded-md bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">
                    ⬆ {{ __('Import All Tables') }}
                </button>
            </form>

            {{-- Truncate All button --}}
            <form method="POST" action="{{ route('admin.import.truncate') }}" class="mt-3" data-confirm="⚠️ {{ __('This will DELETE all data from every table. Are you absolutely sure?') }}" data-confirm-label="{{ __('Truncate') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full rounded-md bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                    🗑 {{ __('Truncate All Tables') }}
                </button>
            </form>

            <div class="my-5 flex items-center gap-3 text-xs text-gray-400">
                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                <span>{{ __('or import a single table') }}</span>
                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
            </div>

            <form method="POST" action="{{ route('admin.import.run') }}" class="space-y-4">
                @csrf

                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    <span class="mb-1 block">{{ __('Table') }}</span>
                    <select name="table" class="form-input-admin">
                        <option value="">{{ __('Choose a table') }}</option>
                        @foreach ($tables as $table)
                            <option value="{{ $table }}" @selected(old('table') === $table)>{{ $table }}</option>
                        @endforeach
                    </select>
                    @error('table')
                        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-200">
                    <input type="checkbox" name="dry_run" value="1" @checked(old('dry_run', true))>
                    <span>{{ __('Dry run only') }}</span>
                </label>

                <button type="submit" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-200">
                    {{ __('Import Selected Table') }}
                </button>
            </form>
        </section>

        <section class="card-admin p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Latest Result') }}</p>
                    <h2 class="mt-1 text-xl font-semibold">{{ __('Import Report') }}</h2>
                </div>
            </div>

            @if ($report)
                <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-950 p-4 text-xs leading-6 text-gray-100">{{ json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <div class="mt-4 rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    {{ __('No import report yet.') }}
                </div>
            @endif
        </section>
    </div>
@endsection
