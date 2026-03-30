@extends('public.layout')

@section('content')
    <section class="space-y-8">
        <header class="public-fade-up rounded-[2rem] border border-gray-200 bg-gradient-to-b from-white to-[#f5f5f7] p-8 dark:border-gray-800 dark:from-gray-900 dark:to-gray-950">
            <p class="text-xs uppercase tracking-[0.2em] text-gray-500">{{ __('Timeline') }}</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1d1d1f] dark:text-gray-100 md:text-5xl">{{ __('Milestones and process events') }}</h1>
        </header>

        <div class="space-y-4">
            @forelse ($events as $event)
                <article class="public-card p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-gray-500">{{ ucfirst($event->event_type) }}</p>
                            <h2 class="mt-1 text-xl font-semibold text-[#1d1d1f] dark:text-gray-100">{{ $event->title }}</h2>
                            <p class="mt-2 text-sm text-gray-500">{{ optional($event->start_date)->format('d.m.Y') }}@if($event->end_date) → {{ optional($event->end_date)->format('d.m.Y') }}@endif</p>
                        </div>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium" style="background-color: {{ $event->color }}22; color: {{ $event->color }};">{{ $event->category ?: __('General') }}</span>
                    </div>
                    @if ($event->description)
                        <p class="mt-4 text-sm leading-relaxed text-gray-700 dark:text-gray-200">{{ $event->description }}</p>
                    @endif
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-gray-300 p-8 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ __('No public timeline events yet.') }}</div>
            @endforelse
        </div>
    </section>
@endsection