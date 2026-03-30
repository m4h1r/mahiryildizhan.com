@extends('admin.layout')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[420px_minmax(0,1fr)]">
        <section class="card-admin p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">CSV Import Pipeline</p>
            <h2 class="mt-1 text-xl font-semibold">Run Import</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Development files are read from <strong>database/csv</strong> first, then <strong>public/csv</strong>.
                Production reads from <strong>storage/app/import</strong>.
            </p>

            <form method="POST" action="{{ route('admin.import.run') }}" class="mt-6 space-y-4">
                @csrf

                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    <span class="mb-1 block">Table</span>
                    <select name="table" class="form-input-admin">
                        <option value="">Choose a table</option>
                        @foreach ($tables as $table)
                            <option value="{{ $table }}" @selected(old('table') === $table)>{{ $table }}</option>
                        @endforeach
                    </select>
                    @error('table')
                        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-200">
                    <input type="checkbox" name="import_all" value="1" @checked(old('import_all'))>
                    <span>Import all tables in roadmap order</span>
                </label>

                <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-200">
                    <input type="checkbox" name="dry_run" value="1" @checked(old('dry_run', true))>
                    <span>Dry run only</span>
                </label>

                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">
                    Start Import
                </button>
            </form>
        </section>

        <section class="card-admin p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Latest Result</p>
                    <h2 class="mt-1 text-xl font-semibold">Import Report</h2>
                </div>
            </div>

            @if ($report)
                <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-950 p-4 text-xs leading-6 text-gray-100">{{ json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <div class="mt-4 rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    No import report yet.
                </div>
            @endif
        </section>
    </div>
@endsection
