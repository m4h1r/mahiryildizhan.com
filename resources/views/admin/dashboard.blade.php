@extends('admin.layout', ['title' => 'Dashboard', 'heading' => 'Dashboard'])

@section('content')
    <section class="grid gap-4 md:grid-cols-3" x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 900)">
        <template x-for="i in 3" :key="i">
            <div x-show="loading" class="h-24 w-full animate-pulse rounded-xl bg-gray-200 dark:bg-gray-700"></div>
        </template>

        <div x-cloak x-show="!loading" class="card-admin">
            <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Published Posts</h2>
            <p class="mt-2 text-2xl font-semibold">0</p>
        </div>

        <div x-cloak x-show="!loading" class="card-admin">
            <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Comments</h2>
            <p class="mt-2 text-2xl font-semibold">0</p>
        </div>

        <div x-cloak x-show="!loading" class="card-admin">
            <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Net</h2>
            <p class="mt-2 text-2xl font-semibold">0 TRY</p>
        </div>
    </section>
@endsection
