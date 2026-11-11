{{-- Five-layer personality & relationship analysis. Rendered for every person; empty layers invite an assessment. --}}
@php
    /** @var \App\Models\PersonAssessment|null $assessment */
    $bigFive = $assessment?->layerRows('big_five') ?? [];
    $basesRows = $assessment?->layerRows('relationship_bases') ?? [];
    $metricRows = $assessment?->layerRows('metrics') ?? [];
    $valueRows = $assessment?->layerRows('values') ?? [];
    $darkRows = $assessment?->layerRows('dark_triad') ?? [];
    $approachLabels = config('crm.approach_lists');
    $hasApproach = $assessment && collect(array_keys($approachLabels))->contains(fn ($k) => $assessment->approachList($k) !== []);
@endphp

@if (! $assessment)
    <div class="card-admin">
        <h3 class="text-base font-semibold text-ink">{{ __('No analysis yet') }}</h3>
        <p class="mt-1 max-w-prose text-sm text-ink-muted">
            {{ __('The card has room for five layers: Big Five personality, relationship bases, relationship metrics, attachment style with values and the dark side, and how to approach this person. Add one yourself, or let Alice fill it in through the API.') }}
        </p>
        <a href="{{ route('admin.people.assessments.create', $person) }}" class="admin-btn admin-btn-primary mt-4">{{ __('Add assessment') }}</a>
    </div>
@endif

@if ($assessment)
    {{-- Snapshot bar --}}
    <div class="glass flex flex-wrap items-center justify-between gap-3 rounded-2xl px-4 py-3">
        <div class="min-w-0 text-sm text-ink-soft">
            <span class="font-semibold text-ink">{{ $assessment->assessed_at?->translatedFormat('j F Y') }}</span>
            <span class="mx-1.5 text-ink-muted">/</span>
            {{ __(ucfirst($assessment->source)) }}
            @if ($assessment->source_message_count)
                <span class="mx-1.5 text-ink-muted">/</span>{{ trans_choice(':count message|:count messages', $assessment->source_message_count, ['count' => number_format($assessment->source_message_count, 0, ',', '.')]) }}
            @endif
            @if ($assessment->relationship_label)
                <span class="mx-1.5 text-ink-muted">/</span>{{ $assessment->relationship_label }}
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.people.assessments.edit', [$person, $assessment]) }}" class="admin-btn-sm admin-btn-ghost">{{ __('Edit') }}</a>
            <a href="{{ route('admin.people.assessments.create', $person) }}" class="admin-btn-sm admin-btn-primary">{{ __('New assessment') }}</a>
        </div>
    </div>

    @if ($assessment->summary)
        <article class="card-admin">
            <h3 class="text-base font-semibold text-ink">{{ __('Summary') }}</h3>
            <div class="admin-prose mt-2 max-w-prose">{{ \App\Models\PersonAssessment::markdown($assessment->summary) }}</div>
        </article>
    @endif

    {{-- Layer 1 + 2: Big Five and relationship bases --}}
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
        <article class="card-admin min-w-0">
            <h3 class="text-base font-semibold text-ink">{{ __('Big Five personality') }}</h3>
            @if ($bigFive)
                <div class="mt-3 space-y-5">
                    <div class="relative mx-auto h-64 w-full max-w-md" x-data="bigFiveRadar(@js($bigFive))">
                        <canvas x-ref="canvas" role="img" aria-label="{{ __('Big Five radar chart') }}"></canvas>
                    </div>
                    <div class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
                        @foreach ($bigFive as $row)
                            @include('admin.people.card._meter', ['row' => $row])
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-3">@include('admin.people.card._empty', ['message' => __('No Big Five scores in this assessment.')])</div>
            @endif
        </article>

        <article class="card-admin min-w-0">
            <h3 class="text-base font-semibold text-ink">{{ __('Relationship bases') }}</h3>
            <div class="mt-3 space-y-3">
                @forelse ($basesRows as $row)
                    @include('admin.people.card._meter', ['row' => $row])
                @empty
                    @include('admin.people.card._empty', ['message' => __('No relationship bases scored yet.')])
                @endforelse
            </div>
        </article>
    </div>

    {{-- Layer 3: relationship metrics --}}
    <article class="card-admin">
        <h3 class="text-base font-semibold text-ink">{{ __('Relationship metrics') }}</h3>
        @if ($metricRows)
            <div class="mt-3 grid gap-x-8 gap-y-4 md:grid-cols-2">
                @foreach ($metricRows as $row)
                    @include('admin.people.card._meter', ['row' => $row])
                @endforeach
            </div>
        @else
            <div class="mt-3">@include('admin.people.card._empty', ['message' => __('No relationship metrics yet.')])</div>
        @endif
    </article>

    {{-- Layer 4: attachment, values, dark side --}}
    <div class="grid gap-5 lg:grid-cols-3">
        <article class="card-admin min-w-0">
            <h3 class="text-base font-semibold text-ink">{{ __('Attachment style') }}</h3>
            @if ($assessment->attachment_style)
                <p class="mt-3"><span class="chip chip-brand text-sm">{{ __(config('crm.attachment_styles.'.$assessment->attachment_style)) }}</span></p>
            @endif
            @if ($assessment->attachment_note)
                <div class="admin-prose mt-3">{{ \App\Models\PersonAssessment::markdown($assessment->attachment_note) }}</div>
            @endif
            @if (! $assessment->attachment_style && ! $assessment->attachment_note)
                <div class="mt-3">@include('admin.people.card._empty', ['message' => __('Not enough observations for a clear style yet.')])</div>
            @endif
        </article>

        <article class="card-admin min-w-0">
            <h3 class="text-base font-semibold text-ink">{{ __('Values (Schwartz)') }}</h3>
            <div class="mt-3 space-y-3">
                @forelse ($valueRows as $row)
                    @include('admin.people.card._meter', ['row' => $row])
                @empty
                    @include('admin.people.card._empty', ['message' => __('No values scored yet.')])
                @endforelse
            </div>
        </article>

        <article class="card-admin min-w-0">
            <h3 class="text-base font-semibold text-ink">{{ __('Dark side (Dark Triad)') }}</h3>
            <div class="mt-3 space-y-3">
                @foreach ($darkRows as $row)
                    @include('admin.people.card._meter', ['row' => $row])
                @endforeach
                @if ($assessment->dark_triad_note)
                    <div class="admin-prose">{{ \App\Models\PersonAssessment::markdown($assessment->dark_triad_note) }}</div>
                @endif
                @if (! $darkRows && ! $assessment->dark_triad_note)
                    @include('admin.people.card._empty', ['message' => __('No signs recorded.')])
                @endif
            </div>
        </article>
    </div>

    {{-- Layer 5: approach --}}
    <article class="card-admin">
        <h3 class="text-base font-semibold text-ink">{{ __('How to approach') }}</h3>
        @if ($hasApproach)
            <div class="mt-3 grid gap-5 md:grid-cols-3">
                @foreach ($approachLabels as $key => $label)
                    <div class="min-w-0">
                        <h4 class="text-sm font-semibold text-ink-soft">{{ __($label) }}</h4>
                        <ul class="mt-2 space-y-2">
                            @forelse ($assessment->approachList($key) as $item)
                                <li class="glass-well px-3 py-2 text-sm leading-relaxed text-ink-soft">{{ $item }}</li>
                            @empty
                                <li class="text-sm text-ink-muted">—</li>
                            @endforelse
                        </ul>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-3">@include('admin.people.card._empty', ['message' => __('Nothing noted on approach, triggers or red flags yet.')])</div>
        @endif
    </article>

    @if ($assessment->notes)
        <article class="card-admin">
            <h3 class="text-base font-semibold text-ink">{{ __('Notes') }}</h3>
            <div class="admin-prose mt-2 max-w-prose">{{ \App\Models\PersonAssessment::markdown($assessment->notes) }}</div>
        </article>
    @endif
@endif

{{-- History --}}
@if ($person->assessments->isNotEmpty())
    <article class="admin-table-shell">
        <div class="border-b border-glass px-4 py-3">
            <h3 class="text-sm font-semibold text-ink">{{ __('Assessment history') }}</h3>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Source') }}</th>
                    <th>{{ __('Confidence') }}</th>
                    <th>{{ __('Note') }}</th>
                    <th class="text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($person->assessments as $item)
                    <tr @if ($assessment && $item->id === $assessment->id) class="bg-[var(--brand-wash)]" @endif>
                        <td class="whitespace-nowrap admin-tabular">
                            <a href="{{ route('admin.people.show', [$person, 'tab' => 'analysis', 'assessment' => $item->id]) }}" class="font-semibold text-[var(--brand-ink)] hover:underline">{{ $item->assessed_at?->format('Y-m-d') }}</a>
                        </td>
                        <td>{{ __(ucfirst($item->source)) }}</td>
                        <td class="admin-tabular">{{ $item->overall_confidence !== null ? '%'.(int) round($item->overall_confidence * 100) : '—' }}</td>
                        <td class="min-w-[12rem] text-xs text-ink-soft">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item->history_note), 140) ?: '—' }}</td>
                        <td>
                            <div class="flex justify-end gap-1.5">
                                <a href="{{ route('admin.people.assessments.edit', [$person, $item]) }}" class="admin-btn-sm admin-btn-ghost">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.people.assessments.destroy', [$person, $item]) }}" data-confirm="{{ __('Delete this assessment? It moves to the trash.') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn-sm admin-btn-danger">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </article>
@endif
