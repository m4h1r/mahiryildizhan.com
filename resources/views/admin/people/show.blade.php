@extends('admin.layout', ['title' => 'Person card', 'heading' => 'Person card'])

@php
    /** @var \App\Models\Person $person */
    /** @var \App\Models\PersonAssessment|null $assessment */
    $tabs = [
        'identity' => __('Identity'),
        'contact' => __('Contact'),
        'character' => __('Character'),
        'analysis' => __('Analysis'),
        'faces' => __('Face data'),
        'interactions' => __('Interactions'),
    ];
    $initialTab = array_key_exists(request('tab'), $tabs) ? request('tab') : 'identity';
    if ($errors->has('image') || $errors->has('embedding') || $errors->has('embedding_json')) {
        $initialTab = 'faces';
    }
    $confidence = $assessment?->overall_confidence;
    $bases = collect($person->relationship_bases ?? [])->map(fn ($b) => __(config("crm.relationship_bases.$b", $b)));
    $md = fn ($text) => \App\Models\PersonAssessment::markdown($text);
@endphp

@section('content')
<div class="mx-auto max-w-6xl space-y-5" x-data="{ tab: @js($initialTab) }" x-init="$watch('tab', t => { const u = new URL(location); u.searchParams.set('tab', t); history.replaceState(null, '', u); })">

    {{-- ================= Header ================= --}}
    <section class="card-admin condensation overflow-hidden">
        <div class="flex flex-col gap-5 md:flex-row md:items-center">
            <div class="flex min-w-0 flex-1 items-center gap-4 sm:gap-5">
                <img
                    src="{{ $person->avatarUrl() }}"
                    alt=""
                    class="h-20 w-20 shrink-0 rounded-[26px] border border-[var(--glass-edge)] object-cover shadow-[var(--glass-shadow)] sm:h-24 sm:w-24"
                >
                <div class="min-w-0">
                    <h2 class="truncate text-2xl font-semibold tracking-tight text-ink sm:text-[28px]">
                        {{ $person->fullName() }}
                    </h2>
                    <p class="mt-0.5 truncate text-sm text-ink-muted">
                        @if ($person->nickname)“{{ $person->nickname }}”@endif
                        @if ($person->nickname && ($person->job_title || $person->company)) — @endif
                        {{ collect([$person->job_title, $person->company])->filter()->implode(', ') }}
                    </p>
                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        @if ($person->scopeLabel())
                            <span class="chip chip-brand">{{ $person->scopeLabel() }}</span>
                        @endif
                        @foreach ($bases as $base)
                            <span class="chip">{{ $base }}</span>
                        @endforeach
                        @if ($person->relationship_label)
                            <span class="chip">{{ $person->relationship_label }}</span>
                        @endif
                        @if ($person->deathday)
                            <span class="chip">✝ {{ $person->deathday->format('Y') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <dl class="grid shrink-0 grid-cols-3 gap-2 text-center md:w-[22rem]">
                <div class="glass-well px-2 py-3">
                    <dt class="text-[11px] font-medium text-ink-muted">{{ __('Confidence') }}</dt>
                    <dd class="mt-1 text-lg font-semibold text-ink admin-tabular">{{ $confidence !== null ? '%'.(int) round($confidence * 100) : '—' }}</dd>
                </div>
                <div class="glass-well px-2 py-3">
                    <dt class="text-[11px] font-medium text-ink-muted">{{ __('Last contact') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-ink admin-tabular">{{ $lastInteractionAt ? \Illuminate\Support\Carbon::parse($lastInteractionAt)->translatedFormat('j M Y') : '—' }}</dd>
                </div>
                <div class="glass-well px-2 py-3">
                    <dt class="text-[11px] font-medium text-ink-muted">{{ $person->age() !== null ? __('Age') : __('Zodiac') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-ink">
                        @if ($person->age() !== null) {{ $person->age() }} @endif
                        {{ $person->zodiacSymbol() }}
                        @if ($person->age() === null && ! $person->zodiacSymbol()) — @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="mt-5 flex flex-wrap gap-2 border-t border-glass pt-4">
            <a href="{{ route('admin.people.edit', $person) }}" class="admin-btn admin-btn-primary">{{ __('Edit card') }}</a>
            <a href="{{ route('admin.people.assessments.create', $person) }}" class="admin-btn admin-btn-ghost">{{ __('New assessment') }}</a>
            <a href="{{ route('admin.interactions.create', ['person_id' => $person->id]) }}" class="admin-btn admin-btn-ghost">{{ __('New Interaction') }}</a>
            <a href="{{ route('admin.people.graph', $person) }}" class="admin-btn admin-btn-ghost">{{ __('Family tree') }}</a>
        </div>
    </section>

    {{-- ================= Tabs ================= --}}
    <nav class="glass sticky top-[calc(var(--shell-gap)+3.5rem+0.5rem)] z-20 -mx-1 overflow-x-auto rounded-2xl p-1 [scrollbar-width:none]" aria-label="{{ __('Person card sections') }}">
        <div class="flex min-w-max gap-1" role="tablist">
            @foreach ($tabs as $key => $label)
                <button
                    type="button"
                    role="tab"
                    id="tab-{{ $key }}"
                    aria-controls="panel-{{ $key }}"
                    :aria-selected="(tab === '{{ $key }}').toString()"
                    @click="tab = '{{ $key }}'"
                    class="rounded-xl px-3.5 py-2 text-sm font-medium transition-colors"
                    :class="tab === '{{ $key }}' ? 'bg-[var(--glass-strong)] text-ink shadow-sm' : 'text-ink-muted hover:text-ink'"
                >
                    {{ $label }}
                    @if ($key === 'faces' && $person->faces->count())
                        <span class="ml-1 text-xs text-ink-muted admin-tabular">{{ $person->faces->count() }}</span>
                    @endif
                    @if ($key === 'analysis' && $person->assessments->count())
                        <span class="ml-1 text-xs text-ink-muted admin-tabular">{{ $person->assessments->count() }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </nav>

    {{-- ================= Identity ================= --}}
    <section id="panel-identity" role="tabpanel" aria-labelledby="tab-identity" x-show="tab === 'identity'" class="card-admin">
        @php
            $identity = [
                __('Name') => $person->name,
                __('Surname') => $person->surname,
                __('Maiden name') => $person->second_surname,
                __('Nickname') => $person->nickname,
                __('Gender') => $person->gender ? __($person->gender->name) : null,
                __('Date of birth') => $person->birthLabel(),
                __('Zodiac') => $person->zodiacName() ? $person->zodiacSymbol().' '.$person->zodiacName() : null,
                __('Birth Place') => $person->birth_place,
                __('Date of death') => $person->deathday?->translatedFormat('j F Y'),
                __('Death Place') => $person->death_place,
                __('Eye Color') => $person->eyeColor ? __($person->eyeColor->name) : null,
                __('Hair Color') => $person->hairColor ? __($person->hairColor->name) : null,
                __('Blood Type') => $person->blood_type,
                __('Company') => $person->company,
                __('Job title') => $person->job_title,
                __('Met on') => collect([$person->met_at?->translatedFormat('j F Y'), $person->met_how])->filter()->implode(' — ') ?: null,
            ];
        @endphp
        <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($identity as $label => $value)
                <div class="min-w-0">
                    <dt class="text-xs font-medium text-ink-muted">{{ $label }}</dt>
                    <dd class="mt-0.5 break-words text-sm text-ink">{{ $value ?: '—' }}</dd>
                </div>
            @endforeach
        </dl>

        <div class="mt-6 grid gap-3 border-t border-glass pt-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (['father' => __('Father'), 'mother' => __('Mother'), 'partner' => __('Partner')] as $relation => $label)
                <div class="glass-well px-4 py-3">
                    <p class="text-xs font-medium text-ink-muted">{{ $label }}</p>
                    @if ($person->{$relation})
                        <a href="{{ route('admin.people.show', $person->{$relation}) }}" class="mt-0.5 block truncate text-sm font-semibold text-[var(--brand-ink)] hover:underline">{{ $person->{$relation}->fullName() }}</a>
                    @else
                        <p class="mt-0.5 text-sm text-ink">—</p>
                    @endif
                </div>
            @endforeach
            <div class="glass-well px-4 py-3">
                <p class="text-xs font-medium text-ink-muted">{{ __('Business record') }}</p>
                @if ($person->stakeholder)
                    <a href="{{ route('admin.stakeholders.edit', $person->stakeholder) }}" class="mt-0.5 block truncate text-sm font-semibold text-[var(--brand-ink)] hover:underline">{{ $person->stakeholder->title ?: trim($person->stakeholder->name.' '.$person->stakeholder->surname) }}</a>
                @else
                    <p class="mt-0.5 text-sm text-ink">—</p>
                @endif
            </div>
        </div>

        @if ($children->isNotEmpty())
            <div class="mt-5">
                <p class="mb-2 text-xs font-medium text-ink-muted">{{ __('Children') }}</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($children as $child)
                        <a href="{{ route('admin.people.show', $child) }}" class="chip hover:text-ink">{{ $child->fullName() }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- ================= Contact ================= --}}
    <section id="panel-contact" role="tabpanel" aria-labelledby="tab-contact" x-show="tab === 'contact'" x-cloak class="card-admin">
        <div class="grid gap-3 sm:grid-cols-2">
            @if ($person->mobile)
                <a href="tel:{{ preg_replace('/[^\d+]/', '', $person->mobile) }}" class="glass-well block px-4 py-3 hover:border-[var(--field-line)]">
                    <span class="block text-xs font-medium text-ink-muted">{{ __('Phone') }}</span>
                    <span class="block truncate text-sm font-semibold text-ink">{{ $person->mobile }}</span>
                </a>
            @endif
            @if ($person->email)
                <a href="mailto:{{ $person->email }}" class="glass-well block px-4 py-3 hover:border-[var(--field-line)]">
                    <span class="block text-xs font-medium text-ink-muted">{{ __('Email') }}</span>
                    <span class="block truncate text-sm font-semibold text-ink">{{ $person->email }}</span>
                </a>
            @endif
            @foreach ($person->channels as $channel)
                @php $href = $channel->href(); @endphp
                <{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" target="_blank" rel="noopener noreferrer" @endif class="glass-well block px-4 py-3 hover:border-[var(--field-line)]">
                    <span class="flex items-center gap-1.5 text-xs font-medium text-ink-muted">
                        {{ $channel->typeLabel() }}
                        @if ($channel->label) <span>· {{ $channel->label }}</span> @endif
                        @if ($channel->is_primary) <span class="chip chip-brand min-h-5 px-1.5 text-[10px]">{{ __('Primary') }}</span> @endif
                    </span>
                    <span class="block truncate text-sm font-semibold text-ink">{{ $channel->value }}</span>
                </{{ $href ? 'a' : 'div' }}>
            @endforeach
        </div>
        @if (! $person->mobile && ! $person->email && $person->channels->isEmpty())
            @include('admin.people.card._empty', ['message' => __('No contact details yet. Add a phone, an e-mail or social profiles from Edit card.')])
        @endif
        @if ($person->address)
            <div class="mt-4 glass-well px-4 py-3">
                <p class="text-xs font-medium text-ink-muted">{{ __('Address') }}</p>
                <p class="mt-0.5 whitespace-pre-line text-sm text-ink">{{ $person->address }}</p>
            </div>
        @endif
    </section>

    {{-- ================= Character ================= --}}
    <section id="panel-character" role="tabpanel" aria-labelledby="tab-character" x-show="tab === 'character'" x-cloak class="card-admin space-y-5">
        <div>
            <h3 class="text-sm font-semibold text-ink">{{ __('Character') }}</h3>
            @if ($person->character_summary)
                <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $person->character_summary }}</p>
            @else
                <p class="mt-1.5 text-sm text-ink-muted">{{ __('Not written yet.') }}</p>
            @endif
        </div>
        @foreach (['interests' => __('Curiosities and interests'), 'hobbies' => __('Hobbies'), 'tendencies' => __('Tendencies')] as $field => $label)
            <div>
                <h3 class="text-sm font-semibold text-ink">{{ $label }}</h3>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @forelse ((array) $person->{$field} as $tag)
                        <span class="chip">{{ $tag }}</span>
                    @empty
                        <span class="text-sm text-ink-muted">—</span>
                    @endforelse
                </div>
            </div>
        @endforeach
        @if ($person->notes)
            <div class="border-t border-glass pt-5">
                <h3 class="text-sm font-semibold text-ink">{{ __('Notes') }}</h3>
                <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $person->notes }}</p>
            </div>
        @endif
    </section>

    {{-- ================= Analysis (five layers) ================= --}}
    <section id="panel-analysis" role="tabpanel" aria-labelledby="tab-analysis" x-show="tab === 'analysis'" x-cloak class="space-y-5">
        @include('admin.people.card._analysis')
    </section>

    {{-- ================= Face data ================= --}}
    <section id="panel-faces" role="tabpanel" aria-labelledby="tab-faces" x-show="tab === 'faces'" x-cloak class="space-y-5">
        @include('admin.people.card._faces')
    </section>

    {{-- ================= Interactions ================= --}}
    <section id="panel-interactions" role="tabpanel" aria-labelledby="tab-interactions" x-show="tab === 'interactions'" x-cloak class="admin-table-shell">
        <div class="flex items-center justify-between gap-3 border-b border-glass px-4 py-3">
            <h3 class="text-sm font-semibold text-ink">{{ __('Interactions') }}</h3>
            <a href="{{ route('admin.interactions.create', ['person_id' => $person->id]) }}" class="admin-btn-sm admin-btn-primary">{{ __('New Interaction') }}</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Effect') }}</th>
                    <th>{{ __('Notes') }}</th>
                    <th class="text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($interactions as $interaction)
                    <tr>
                        <td class="whitespace-nowrap admin-tabular">{{ optional($interaction->date)->format('Y-m-d') ?: '—' }}</td>
                        <td>{{ optional($interaction->type)->name ?: '—' }}</td>
                        <td>{{ $interaction->effect ?: '—' }}</td>
                        <td class="min-w-[14rem] text-xs text-ink-soft">{{ \Illuminate\Support\Str::limit((string) $interaction->notes, 120) ?: '—' }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.interactions.edit', $interaction) }}" class="admin-btn-sm admin-btn-ghost">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-ink-muted">{{ __('No interactions yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($interactions->hasPages())
            <div class="border-t border-glass px-4 py-3">{{ $interactions->appends(['tab' => 'interactions'])->links() }}</div>
        @endif
    </section>
</div>
@endsection
