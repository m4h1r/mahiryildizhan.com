{{-- Searchable person picker. Params: $field, $label, $current (?Person), $excludeId --}}
@php
    $currentId = old($field, $current?->id);
    $currentLabel = $current && (int) $currentId === $current->id ? $current->fullName() : ($currentId ? \App\Models\Person::find($currentId)?->fullName() : null);
@endphp
<div
    class="relative"
    x-data="personPicker({
        url: @js(route('admin.people.search')),
        excludeId: @js($excludeId),
        selected: @js($currentId ? ['id' => (int) $currentId, 'label' => $currentLabel] : null),
    })"
    @click.outside="open = false"
>
    <span class="mb-1 block text-sm font-medium text-ink-soft" id="{{ $field }}-label">{{ $label }}</span>
    <input type="hidden" name="{{ $field }}" :value="selected ? selected.id : ''">

    <template x-if="selected">
        <div class="form-input-admin flex items-center justify-between gap-2">
            <span class="truncate" x-text="selected.label"></span>
            <button type="button" class="admin-btn-sm admin-btn-ghost -mr-1.5 h-7 w-7 p-0" @click="clear()" aria-label="{{ __('Remove') }}: {{ $label }}">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
    </template>

    <template x-if="!selected">
        <input
            type="search"
            x-ref="input"
            x-model="query"
            @input="search()"
            @keydown.arrow-down.prevent="move(1)"
            @keydown.arrow-up.prevent="move(-1)"
            @keydown.enter.prevent="enter()"
            @keydown.escape="open = false"
            class="form-input-admin"
            placeholder="{{ __('Type at least 2 letters') }}"
            role="combobox"
            aria-labelledby="{{ $field }}-label"
            :aria-expanded="open.toString()"
            autocomplete="off"
        >
    </template>

    <ul
        x-show="open"
        x-cloak
        x-transition.opacity.duration.120ms
        class="glass-strong absolute inset-x-0 top-full z-30 mt-1 max-h-64 overflow-y-auto rounded-xl p-1"
        role="listbox"
    >
        <template x-for="(person, index) in results" :key="person.id">
            <li>
                <button
                    type="button"
                    @click="choose(person)"
                    @mouseenter="active = index"
                    class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm"
                    :class="active === index ? 'bg-[var(--brand-wash)] text-ink' : 'text-ink-soft'"
                    role="option"
                    :aria-selected="(active === index).toString()"
                >
                    <span class="truncate" x-text="person.label"></span>
                    <span class="shrink-0 text-xs text-ink-muted admin-tabular" x-text="person.year"></span>
                </button>
            </li>
        </template>
        <li x-show="!results.length" class="px-3 py-2 text-sm text-ink-muted">{{ __('No match found.') }}</li>
    </ul>
</div>
