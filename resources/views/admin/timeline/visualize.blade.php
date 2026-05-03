@extends('admin.layout', ['title' => 'Timeline', 'heading' => 'Timeline'])

@section('content')
<div class="space-y-4" x-data="{ modalOpen: false, modalEvent: null }">

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
                            $modalData = [
                                'title'       => $event->title,
                                'icon'        => $event->icon,
                                'color'       => $color,
                                'event_type'  => $event->event_type,
                                'category'    => $event->category,
                                'location'    => $event->location,
                                'description' => $event->description,
                                'start_date'  => optional($event->start_date)->format('d M Y'),
                                'end_date'    => optional($event->end_date)->format('d M Y'),
                                'ongoing'     => $event->event_type === 'process' && !$event->end_date,
                                'duration'    => $event->end_date ? $event->start_date->diffInDays($event->end_date) : null,
                                'image'       => $imgUrl,
                            ];
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
                        <div class="absolute z-[4] w-72 -translate-y-1/2 cursor-pointer transition-transform duration-[400ms] hover:scale-[1.15] {{ $isLeft ? 'origin-right' : 'origin-left' }}"
                             @click="modalEvent = {{ Illuminate\Support\Js::from($modalData) }}; modalOpen = true"
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
                        $modalData = [
                            'title'       => $event->title,
                            'icon'        => $event->icon,
                            'color'       => $color,
                            'event_type'  => $event->event_type,
                            'category'    => $event->category,
                            'location'    => $event->location,
                            'description' => $event->description,
                            'start_date'  => optional($event->start_date)->format('d M Y'),
                            'end_date'    => optional($event->end_date)->format('d M Y'),
                            'ongoing'     => $event->event_type === 'process' && !$event->end_date,
                            'duration'    => $event->end_date ? $event->start_date->diffInDays($event->end_date) : null,
                            'image'       => $imgUrl,
                        ];
                    @endphp
                    <div class="relative cursor-pointer"
                         @click="modalEvent = {{ Illuminate\Support\Js::from($modalData) }}; modalOpen = true">
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

    {{-- Event Detail Modal --}}
    <div x-show="modalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="modalOpen = false"
         @keydown.escape.window="modalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
         style="display: none">
        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-900">
            <template x-if="modalEvent">
                <div>
                    <template x-if="modalEvent.image">
                        <img :src="modalEvent.image" :alt="modalEvent.title" class="w-full object-cover" style="height: 220px">
                    </template>
                    <div class="p-5">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2">
                                <span x-show="modalEvent.icon" x-text="modalEvent.icon" class="shrink-0 text-xl leading-tight"></span>
                                <h2 x-text="modalEvent.title" class="text-base font-bold text-gray-900 dark:text-gray-100"></h2>
                            </div>
                            <button @click="modalOpen = false"
                                    class="shrink-0 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <p class="mb-3 text-sm text-gray-400 dark:text-gray-500">
                            <span x-text="modalEvent.start_date"></span>
                            <template x-if="modalEvent.end_date">
                                <span>
                                    <span class="mx-1 opacity-60">→</span>
                                    <span x-text="modalEvent.end_date"></span>
                                    <span x-show="modalEvent.duration" class="text-gray-300 dark:text-gray-600"> · <span x-text="modalEvent.duration + 'g'"></span></span>
                                </span>
                            </template>
                            <template x-if="modalEvent.ongoing && !modalEvent.end_date">
                                <span class="ml-1 text-emerald-500 dark:text-emerald-400"> · devam ediyor</span>
                            </template>
                        </p>
                        <div class="mb-4 flex flex-wrap gap-1.5">
                            <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold"
                                  :style="{ backgroundColor: modalEvent.color + '1a', color: modalEvent.color }">
                                <span x-text="modalEvent.event_type === 'process' ? 'Süreç' : 'Milestone'"></span>
                            </span>
                            <template x-if="modalEvent.category">
                                <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-700/50 dark:text-gray-400"
                                      x-text="modalEvent.category"></span>
                            </template>
                            <template x-if="modalEvent.location">
                                <span class="inline-flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                    <svg class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span x-text="modalEvent.location"></span>
                                </span>
                            </template>
                        </div>
                        <template x-if="modalEvent.description">
                            <p x-text="modalEvent.description" class="text-sm leading-relaxed text-gray-500 dark:text-gray-400"></p>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection
