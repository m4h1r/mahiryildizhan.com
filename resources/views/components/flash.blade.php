@if (session('success') || session('error') || session('status'))
    @php
        $message = session('success') ?? session('error') ?? session('status');
        $isError = session()->has('error');
    @endphp

    <div
        class="mb-4 rounded-lg border px-4 py-3 text-sm {{ $isError ? 'border-red-300 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-200' : 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' }}"
        role="status"
        aria-live="polite"
    >
        {{ $message }}
    </div>
@endif
