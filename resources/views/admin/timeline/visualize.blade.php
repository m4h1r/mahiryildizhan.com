@extends('admin.layout', ['title' => 'Timeline', 'heading' => 'Timeline'])

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $events->count() }} olay</p>
        <a href="{{ route('admin.timeline.index') }}" class="admin-btn admin-btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
            </svg>
            Listeye Dön
        </a>
    </div>

    @if($events->isEmpty())
        <div class="rounded-2xl border border-dashed border-[var(--color-admin-border)] py-16 text-center text-sm text-gray-400 dark:border-[var(--color-admin-border-dark)] dark:text-gray-500">
            Henüz timeline olayı yok.
        </div>
    @else

        {{-- ─── Desktop (md+) ─── --}}
        <div class="hidden md:block">
            <div class="overflow-hidden rounded-2xl border bg-[var(--color-admin-card)] shadow-sm dark:bg-[var(--color-admin-card-dark)]"
                 style="border-color: var(--color-admin-border)">
                <div class="relative" style="height: {{ $containerHeight }}px; min-width: 640px">

                    {{-- Vertical axis --}}
                    <div class="pointer-events-none absolute inset-y-0 left-1/2 w-px -translate-x-1/2 bg-gradient-to-b from-transparent via-gray-200 to-transparent dark:via-gray-700/80"></div>

                    {{-- Year markers --}}
                    @foreach($yearMarkers as $marker)
                        <div class="pointer-events-none absolute left-0 right-0 z-[1] flex items-center"
                             style="top: {{ $marker['px'] }}px">
                            <div class="h-px flex-1 bg-gray-100 dark:bg-gray-800"></div>
                            <span class="shrink-0 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400 dark:text-gray-500">{{ $marker['year'] }}</span>
                            <div class="h-px flex-1 bg-gray-100 dark:bg-gray-800"></div>
                        </div>
                    @endforeach

                    {{-- Events --}}
                    @foreach($events as $index => $event)
                        @php
                            $isLeft = $index % 2 === 0;
                            $color  = $event->color ?: '#3B82F6';
                            $isProc = $event->event_type === 'process' && $event->height_px;
                            $midPx  = (int) $event->card_px;
                            $imgUrl = ($event->image && $event->image !== '')
                                ? (str_starts_with($event->image, 'http')
                                    ? $event->image
                                    : asset('storage/' . ltrim($event->image, '/')))
                                : null;
                        @endphp

                        {{-- Process bar --}}
                        @if($isProc)
                            <div class="pointer-events-none absolute left-1/2 z-[2] -translate-x-1/2 rounded-full"
                                 style="top: {{ $event->start_px }}px; height: {{ $event->height_px }}px; width: 4px; background-color: {{ $color }}; opacity: 0.45"></div>
                            <div class="pointer-events-none absolute left-1/2 z-[3] -translate-x-1/2 -translate-y-1/2 h-2.5 w-2.5 rounded-full border-2 border-[var(--color-admin-card)] dark:border-[var(--color-admin-card-dark)]"
                                 style="top: {{ $event->start_px }}px; background-color: {{ $color }}"></div>
                            @if($event->end_px)
                                <div class="pointer-events-none absolute left-1/2 z-[3] -translate-x-1/2 -translate-y-1/2 h-2.5 w-2.5 rounded-full border-2 border-[var(--color-admin-card)] dark:border-[var(--color-admin-card-dark)]"
                                     style="top: {{ $event->end_px }}px; background-color: {{ $color }}"></div>
                            @endif
                        @else
                            {{-- Milestone diamond --}}
                            <div class="pointer-events-none absolute left-1/2 z-[3] -translate-x-1/2 -translate-y-1/2 h-3 w-3 rotate-45 border-2 border-[var(--color-admin-card)] shadow-sm dark:border-[var(--color-admin-card-dark)]"
                                 style="top: {{ $event->start_px }}px; background-color: {{ $color }}"></div>
                        @endif

                        {{-- Connector --}}
                        <div class="pointer-events-none absolute z-[3] h-px opacity-50"
                             style="top: {{ $midPx }}px; {{ $isLeft ? 'right: 50%' : 'left: 50%' }}; width: 28px; background-color: {{ $color }}"></div>

                        {{-- Card --}}
                        <div class="absolute z-[4] w-72 -translate-y-1/2 transition-transform duration-[400ms] hover:scale-[1.15] {{ $isLeft ? 'origin-right' : 'origin-left' }}"
                             style="top: {{ $midPx }}px; {{ $isLeft ? 'right: calc(50% + 28px)' : 'left: calc(50% + 28px)' }}">
                            <div class="overflow-hidden rounded-xl border border-[var(--color-admin-border)] bg-[var(--color-admin-card)] shadow-sm transition-shadow hover:shadow-lg dark:border-[var(--color-admin-border-dark)] dark:bg-[var(--color-admin-card-dark)]"
                                 style="border-left: 3px solid {{ $color }}">

                                @if($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="{{ $event->title }}" class="w-full object-cover" style="height: 100px">
                                @endif

                                <div class="p-3">
                                    <div class="mb-1 flex items-start gap-1.5">
                                        @if($event->icon)
                                            <span class="shrink-0 text-base leading-tight">{{ $event->icon }}</span>
                                        @endif
                                        <h3 class="line-clamp-2 text-[13px] font-semibold leading-snug text-gray-900 dark:text-gray-100">{{ $event->title }}</h3>
                                    </div>

                                    <p class="mb-2 text-[11px] text-gray-400 dark:text-gray-500">
                                        {{ optional($event->start_date)->format('d M Y') }}
                                        @if($event->end_date)
                                            <span class="mx-0.5 opacity-60">→</span>{{ optional($event->end_date)->format('d M Y') }}
                                            @php $dur = $event->start_date->diffInDays($event->end_date) @endphp
                                            <span class="text-gray-300 dark:text-gray-600"> · {{ $dur }}g</span>
                                        @elseif($event->event_type === 'process')
                                            <span class="text-emerald-500 dark:text-emerald-400"> · devam ediyor</span>
                                        @endif
                                    </p>

                                    <div class="mb-2 flex flex-wrap gap-1">
                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-semibold"
                                              style="background-color: {{ $color }}1a; color: {{ $color }}">
                                            {{ $event->event_type === 'process' ? 'Süreç' : 'Milestone' }}
                                        </span>
                                        @if($event->category)
                                            <span class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-500 dark:bg-gray-700/50 dark:text-gray-400">
                                                {{ $event->category }}
                                            </span>
                                        @endif
                                        @if($event->location)
                                            <span class="inline-flex items-center gap-0.5 text-[10px] text-gray-400 dark:text-gray-500">
                                                <svg class="h-2.5 w-2.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $event->location }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($event->description)
                                        <p class="line-clamp-{{ $imgUrl ? '2' : '3' }} text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">{{ $event->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                    @endforeach
                </div>
            </div>
        </div>

        {{-- ─── Mobile (< md) ─── --}}
        <div class="md:hidden">
            <div class="relative space-y-3 pl-6">
                <div class="pointer-events-none absolute bottom-0 left-2.5 top-0 w-px bg-gradient-to-b from-transparent via-gray-200 to-transparent dark:via-gray-700/80"></div>

                @foreach($events as $event)
                    @php
                        $color  = $event->color ?: '#3B82F6';
                        $imgUrl = ($event->image && $event->image !== '')
                            ? (str_starts_with($event->image, 'http')
                                ? $event->image
                                : asset('storage/' . ltrim($event->image, '/')))
                            : null;
                    @endphp
                    <div class="relative">
                        <div class="absolute left-[-14px] top-3.5 h-2.5 w-2.5 -translate-x-1/2 rounded-full border-2 border-[var(--color-admin-bg)] dark:border-[var(--color-admin-bg-dark)]"
                             style="background-color: {{ $color }}"></div>
                        <div class="overflow-hidden rounded-xl border border-[var(--color-admin-border)] bg-[var(--color-admin-card)] shadow-sm dark:border-[var(--color-admin-border-dark)] dark:bg-[var(--color-admin-card-dark)]"
                             style="border-left: 3px solid {{ $color }}">
                            @if($imgUrl)
                                <img src="{{ $imgUrl }}" alt="{{ $event->title }}" class="w-full object-cover" style="height: 120px">
                            @endif
                            <div class="p-3">
                                <div class="mb-1 flex items-start gap-1.5">
                                    @if($event->icon)
                                        <span class="shrink-0">{{ $event->icon }}</span>
                                    @endif
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $event->title }}</h3>
                                </div>
                                <p class="mb-1.5 text-xs text-gray-400 dark:text-gray-500">
                                    {{ optional($event->start_date)->format('d M Y') }}
                                    @if($event->end_date)
                                        <span class="mx-0.5 opacity-60">→</span>{{ optional($event->end_date)->format('d M Y') }}
                                    @endif
                                </p>
                                @if($event->description)
                                    <p class="line-clamp-3 text-xs text-gray-500 dark:text-gray-400">{{ $event->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    @endif
</div>
@endsection
