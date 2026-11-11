@extends('admin.layout', ['title' => $isNew ? 'New assessment' : 'Edit assessment', 'heading' => $isNew ? 'New assessment' : 'Edit assessment'])

@php
    /** @var \App\Models\PersonAssessment $assessment */
    $layers = [
        'big_five' => ['title' => __('Big Five personality'), 'config' => 'big_five', 'confidence' => true, 'text' => 'evidence', 'textLabel' => __('Evidence')],
        'relationship_bases' => ['title' => __('Relationship bases'), 'config' => 'relationship_bases', 'confidence' => false, 'text' => 'note', 'textLabel' => __('Note')],
        'metrics' => ['title' => __('Relationship metrics'), 'config' => 'metrics', 'confidence' => false, 'text' => 'note', 'textLabel' => __('Note')],
        'values' => ['title' => __('Values (Schwartz)'), 'config' => 'schwartz_values', 'confidence' => false, 'text' => 'note', 'textLabel' => __('Note')],
        'dark_triad' => ['title' => __('Dark side (Dark Triad)'), 'config' => 'dark_triad', 'confidence' => false, 'text' => 'note', 'textLabel' => __('Note')],
    ];
    $action = $isNew
        ? route('admin.people.assessments.store', $person)
        : route('admin.people.assessments.update', [$person, $assessment]);
    $labelClass = 'mb-1 block text-sm font-medium text-ink-soft';
@endphp

@section('content')
<form method="POST" action="{{ $action }}" class="mx-auto max-w-5xl space-y-5">
    @csrf
    @unless ($isNew) @method('PUT') @endunless

    <section class="card-admin">
        <div class="flex flex-wrap items-center gap-4">
            <img src="{{ $person->avatarUrl() }}" alt="" class="h-12 w-12 rounded-2xl object-cover">
            <div class="min-w-0">
                <p class="truncate text-lg font-semibold text-ink">{{ $person->fullName() }}</p>
                <p class="text-sm text-ink-muted">{{ $isNew ? __('Starts from the latest assessment; change only what is different.') : __('Changes this snapshot in place.') }}</p>
            </div>
        </div>

        @if ($errors->any())
            <x-status-message type="danger" class="mt-4">{{ __('Some fields need attention.') }} {{ $errors->first() }}</x-status-message>
        @endif

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label>
                <span class="{{ $labelClass }}">{{ __('Assessment date') }}</span>
                <input type="date" name="assessed_at" class="form-input-admin" required value="{{ old('assessed_at', $assessment->assessed_at?->format('Y-m-d')) }}">
            </label>
            <div x-data="{ v: {{ (float) old('overall_confidence', $assessment->overall_confidence ?? 0.5) }} }">
                <span class="{{ $labelClass }}">{{ __('Overall confidence') }} <span class="admin-tabular text-ink" x-text="'%' + Math.round(v * 100)"></span></span>
                <input type="range" name="overall_confidence" min="0" max="1" step="0.05" x-model.number="v" class="form-input-admin mt-3" aria-label="{{ __('Overall confidence') }}">
            </div>
            <label>
                <span class="{{ $labelClass }}">{{ __('Relationship in a few words') }}</span>
                <input name="relationship_label" class="form-input-admin" value="{{ old('relationship_label', $assessment->relationship_label ?? $person->relationship_label) }}">
            </label>
            <label>
                <span class="{{ $labelClass }}">{{ __('Messages analysed') }}</span>
                <input type="number" min="0" name="source_message_count" class="form-input-admin" value="{{ old('source_message_count', $assessment->source_message_count) }}">
            </label>
        </div>
        <input type="hidden" name="source" value="{{ old('source', $assessment->source ?: 'manual') }}">

        <label class="mt-4 block">
            <span class="{{ $labelClass }}">{{ __('Summary') }}</span>
            <textarea name="summary" rows="4" class="form-input-admin min-h-28" placeholder="{{ __('Who they are to you, in a paragraph. Markdown is allowed.') }}">{{ old('summary', $assessment->summary) }}</textarea>
        </label>
    </section>

    @foreach ($layers as $layer => $meta)
        @php $current = (array) old($layer, $assessment->{$layer} ?? []); @endphp
        <section class="card-admin">
            <h2 class="text-base font-semibold text-ink">{{ $meta['title'] }}</h2>
            <p class="mt-0.5 text-sm text-ink-muted">{{ __('Score only what you have seen. Unscored rows are left out.') }}</p>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach (config('crm.'.$meta['config']) as $key => $label)
                    @php $item = (array) ($current[$key] ?? []); @endphp
                    <div
                        class="glass-well p-3"
                        x-data="{ on: {{ isset($item['score']) && $item['score'] !== '' ? 'true' : 'false' }}, score: {{ (int) ($item['score'] ?? 50) }}, conf: {{ (float) ($item['confidence'] ?? 0.5) }} }"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold text-ink">{{ __($label) }}</span>
                            <button type="button" x-show="!on" class="admin-btn-sm admin-btn-ghost" @click="on = true">{{ __('Score') }}</button>
                            <button type="button" x-show="on" x-cloak class="admin-btn-sm admin-btn-ghost" @click="on = false">{{ __('Clear') }}</button>
                        </div>
                        <template x-if="on">
                            <div class="mt-3 space-y-3">
                                <div class="flex items-center gap-3">
                                    <input type="range" min="0" max="100" step="1" x-model.number="score" class="form-input-admin flex-1" aria-label="{{ __($label) }}">
                                    <input type="number" min="0" max="100" name="{{ $layer }}[{{ $key }}][score]" x-model.number="score" class="form-input-admin w-20 text-center admin-tabular" aria-label="{{ __($label) }} {{ __('score') }}">
                                </div>
                                @if ($meta['confidence'])
                                    <div class="flex items-center gap-3">
                                        <span class="w-20 shrink-0 text-xs text-ink-muted">{{ __('Confidence') }} <span class="admin-tabular" x-text="'%' + Math.round(conf * 100)"></span></span>
                                        <input type="range" min="0" max="1" step="0.05" name="{{ $layer }}[{{ $key }}][confidence]" x-model.number="conf" class="form-input-admin flex-1" aria-label="{{ __($label) }} {{ __('confidence') }}">
                                    </div>
                                @endif
                                <textarea name="{{ $layer }}[{{ $key }}][{{ $meta['text'] }}]" rows="2" class="form-input-admin min-h-16 text-sm" placeholder="{{ $meta['textLabel'] }}">{{ $item[$meta['text']] ?? '' }}</textarea>
                            </div>
                        </template>
                    </div>
                @endforeach
            </div>

            @if ($layer === 'dark_triad')
                <label class="mt-4 block">
                    <span class="{{ $labelClass }}">{{ __('Dark side note') }}</span>
                    <textarea name="dark_triad_note" rows="3" class="form-input-admin min-h-20">{{ old('dark_triad_note', $assessment->dark_triad_note) }}</textarea>
                </label>
            @endif
        </section>
    @endforeach

    <section class="card-admin grid gap-4 md:grid-cols-[minmax(0,0.6fr)_minmax(0,1.4fr)]">
        <h2 class="text-base font-semibold text-ink md:col-span-2">{{ __('Attachment style') }}</h2>
        <label>
            <span class="{{ $labelClass }}">{{ __('Style') }}</span>
            <select name="attachment_style" class="form-input-admin">
                <option value="">{{ __('Not clear yet') }}</option>
                @foreach (config('crm.attachment_styles') as $key => $label)
                    <option value="{{ $key }}" @selected(old('attachment_style', $assessment->attachment_style) === $key)>{{ __($label) }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="{{ $labelClass }}">{{ __('Observations') }}</span>
            <textarea name="attachment_note" rows="3" class="form-input-admin min-h-20">{{ old('attachment_note', $assessment->attachment_note) }}</textarea>
        </label>
    </section>

    <section class="card-admin">
        <h2 class="text-base font-semibold text-ink">{{ __('How to approach') }}</h2>
        <p class="mt-0.5 text-sm text-ink-muted">{{ __('One item per line.') }}</p>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            @foreach (config('crm.approach_lists') as $key => $label)
                <label>
                    <span class="{{ $labelClass }}">{{ __($label) }}</span>
                    <textarea name="approach_text[{{ $key }}]" rows="6" class="form-input-admin min-h-36 text-sm">{{ old("approach_text.$key", implode("\n", $assessment->approachList($key))) }}</textarea>
                </label>
            @endforeach
        </div>
    </section>

    <section class="card-admin grid gap-4 md:grid-cols-2">
        <label>
            <span class="{{ $labelClass }}">{{ __('Notes') }}</span>
            <textarea name="notes" rows="5" class="form-input-admin min-h-32">{{ old('notes', $assessment->notes) }}</textarea>
        </label>
        <label>
            <span class="{{ $labelClass }}">{{ __('What changed in this assessment') }}</span>
            <textarea name="history_note" rows="5" class="form-input-admin min-h-32">{{ old('history_note', $isNew ? '' : $assessment->history_note) }}</textarea>
        </label>
    </section>

    <div class="glass-strong sticky bottom-[calc(var(--admin-bottom-nav-height)+env(safe-area-inset-bottom,0px)+1.5rem)] z-20 flex items-center justify-end gap-2 rounded-2xl p-2 md:bottom-3">
        <a href="{{ route('admin.people.show', [$person, 'tab' => 'analysis']) }}" class="admin-btn admin-btn-ghost">{{ __('Cancel') }}</a>
        <button type="submit" class="admin-btn admin-btn-primary">{{ $isNew ? __('Save assessment') : __('Save changes') }}</button>
    </div>
</form>
@endsection
