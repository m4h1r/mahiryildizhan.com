@extends('admin.layout', ['title' => 'Women In Circle', 'heading' => 'Women In Circle'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-red-600">
                        {{ __('Women In Circle') }}
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ __(':people people, :interactions interactions', ['people' => $people->count(), 'interactions' => $totalInteractions]) }})</span>
                    </h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Newest to oldest, by last interaction date.') }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <label class="inline-flex cursor-pointer select-none items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Possible Circle Candidates') }} ({{ $candidates->count() }})</span>
                        <span class="relative inline-flex h-6 w-11 shrink-0 items-center">
                            <input type="checkbox" id="candidates-toggle" class="peer sr-only">
                            <span class="absolute inset-0 rounded-full bg-gray-300 transition-colors duration-200 peer-checked:bg-red-600 dark:bg-gray-700"></span>
                            <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition-transform duration-200 peer-checked:translate-x-5"></span>
                        </span>
                    </label>
                    <a href="{{ route('admin.interactions.index') }}" class="admin-btn admin-btn-ghost">{{ __('Back') }}</a>
                </div>
            </div>

            @if ($people->isEmpty())
                <p class="py-12 text-center text-gray-500 dark:text-gray-400">{{ __('No interactions of this type yet.') }}</p>
            @else
                <div
                    id="circle-stage"
                    class="relative mx-auto aspect-square w-full max-w-[640px] rounded-full bg-[radial-gradient(circle,_rgba(220,38,38,0.08)_0%,_transparent_70%)] ring-1 ring-red-100 dark:ring-red-900/40"
                >
                    @if ($centerPerson)
                        <a
                            href="{{ route('admin.people.show', $centerPerson) }}"
                            class="absolute left-1/2 top-1/2 z-20 block w-28 -translate-x-1/2 -translate-y-1/2 rounded-2xl border-2 border-red-500 bg-white p-2 text-center shadow-lg transition-transform duration-200 ease-out hover:scale-[1.2] hover:shadow-2xl dark:bg-gray-900"
                        >
                            <img
                                src="{{ $centerPerson->picture_url }}"
                                alt="{{ $centerPerson->fullName() }}"
                                class="mx-auto h-20 w-20 rounded-full border-2 border-red-500 object-cover"
                            >
                            <span class="mt-1 block truncate text-xs font-semibold text-red-600 dark:text-red-400">{{ $centerPerson->fullName() }}</span>
                        </a>
                    @endif

                    @foreach ($people as $woman)
                        <div class="circle-card-slot absolute left-0 top-0 z-10">
                            <a
                                href="{{ route('admin.people.show', $woman) }}"
                                class="circle-card block w-24 -translate-x-1/2 -translate-y-1/2 rounded-xl border border-red-200 bg-white p-2 text-center shadow-md transition-transform duration-200 ease-out hover:z-10 hover:scale-[1.2] hover:shadow-xl dark:border-red-900/50 dark:bg-gray-900"
                            >
                                <img
                                    src="{{ $woman->picture_url }}"
                                    alt="{{ $woman->fullName() }}"
                                    class="mx-auto h-16 w-16 rounded-full border-2 border-red-300 object-cover dark:border-red-700"
                                >
                                <span class="mt-1 block truncate text-xs font-medium text-gray-900 dark:text-gray-100">{{ $woman->fullName() }}</span>
                            </a>
                        </div>
                    @endforeach

                    @foreach ($candidates as $candidate)
                        <div class="candidate-card-slot absolute left-0 top-0 hidden">
                            <a
                                href="{{ route('admin.people.show', $candidate) }}"
                                class="candidate-card block w-24 -translate-x-1/2 -translate-y-1/2 rounded-xl border border-amber-300 bg-white p-2 text-center shadow-md transition-transform duration-200 ease-out hover:z-10 hover:scale-[1.2] hover:shadow-xl dark:border-amber-700/60 dark:bg-gray-900"
                            >
                                <img
                                    src="{{ $candidate->picture_url }}"
                                    alt="{{ $candidate->fullName() }}"
                                    class="mx-auto h-16 w-16 rounded-full border-2 border-amber-300 object-cover dark:border-amber-700"
                                >
                                <span class="mt-1 block truncate text-xs font-medium text-gray-900 dark:text-gray-100">
                                    <span class="font-semibold {{ $candidate->effectScore >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{ $candidate->effectScore >= 0 ? '+' : '' }}{{ $candidate->effectScore }}</span>
                                    {{ $candidate->fullName() }}
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
        (function () {
            const stage = document.getElementById('circle-stage');

            if (!stage) {
                return;
            }

            const cardHalf = 48;
            const centerCardHalf = 56;
            const ringGap = 28;
            const expandedMaxWidth = '920px';

            const placeOnCircle = function (slots, radius, centerX, centerY) {
                const count = slots.length;

                if (!count) {
                    return;
                }

                slots.forEach(function (slot, index) {
                    const angle = ((2 * Math.PI * index) / count) - (Math.PI / 2);
                    slot.style.left = (centerX + radius * Math.cos(angle)) + 'px';
                    slot.style.top = (centerY + radius * Math.sin(angle)) + 'px';
                });
            };

            const layout = function () {
                const innerSlots = stage.querySelectorAll('.circle-card-slot');
                const candidateSlots = stage.querySelectorAll('.candidate-card-slot');
                const expanded = stage.classList.contains('circle-stage--expanded');

                const centerX = stage.clientWidth / 2;
                const centerY = stage.clientHeight / 2;
                const half = Math.min(centerX, centerY);

                const outerRadius = Math.max(half - cardHalf - 12, centerCardHalf + cardHalf + 16);
                const innerRadius = expanded
                    ? Math.max(Math.min(outerRadius - (cardHalf * 2) - ringGap, half * 0.55), centerCardHalf + cardHalf + 16)
                    : outerRadius;

                placeOnCircle(innerSlots, innerRadius, centerX, centerY);

                if (expanded) {
                    placeOnCircle(candidateSlots, outerRadius, centerX, centerY);
                }
            };

            const syncExpanded = function (expanded) {
                stage.classList.toggle('circle-stage--expanded', expanded);
                stage.style.maxWidth = expanded ? expandedMaxWidth : '';

                stage.querySelectorAll('.candidate-card-slot').forEach(function (slot) {
                    slot.classList.toggle('hidden', !expanded);
                });
            };

            const toggle = document.getElementById('candidates-toggle');

            if (toggle) {
                syncExpanded(toggle.checked);

                toggle.addEventListener('change', function () {
                    syncExpanded(toggle.checked);
                    layout();
                });
            }

            window.addEventListener('resize', layout);
            layout();
        })();
    </script>
@endsection
