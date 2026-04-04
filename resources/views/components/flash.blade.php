@if (session('success') || session('error') || session('status'))
    @php
        $message = session('success') ?? session('error') ?? session('status');
        $isError = session()->has('error');
    @endphp

    <div
        x-data="{ visible: true }"
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="mb-4 flex items-start justify-between gap-3 rounded-xl border px-4 py-3 text-sm {{ $isError ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/50 dark:text-red-300' : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300' }}"
        role="status"
        aria-live="polite"
    >
        <span>{{ $message }}</span>
        <button
            type="button"
            @click="visible = false"
            class="shrink-0 opacity-60 hover:opacity-100 transition-opacity"
            aria-label="Dismiss"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif
