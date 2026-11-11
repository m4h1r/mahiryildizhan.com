{{-- Chip input. Params: $field, $label, $values (array), $hint --}}
<div x-data="chipInput(@js(array_values(old($field, $values ?? []) ?? [])))">
    <label for="{{ $field }}-draft" class="mb-1 block text-sm font-medium text-ink-soft">{{ $label }}</label>
    <div class="form-input-admin flex min-h-11 flex-wrap items-center gap-1.5 py-1.5" @click="$refs.draft.focus()">
        <template x-for="(tag, index) in tags" :key="tag + index">
            <span class="chip chip-brand">
                <span x-text="tag"></span>
                <input type="hidden" name="{{ $field }}[]" :value="tag">
                <button type="button" class="-mr-1 rounded-full p-0.5 hover:bg-black/10" @click.stop="remove(index)" :aria-label="'{{ __('Remove') }}: ' + tag">
                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </span>
        </template>
        <input
            id="{{ $field }}-draft"
            x-ref="draft"
            x-model="draft"
            @keydown.enter.prevent="add()"
            @keydown.comma.prevent="add()"
            @keydown.backspace="backspace()"
            @blur="add()"
            class="min-w-[8rem] flex-1 border-0 bg-transparent p-0 text-sm text-ink outline-none focus:ring-0"
            placeholder="{{ $hint ?? __('Type and press Enter') }}"
        >
    </div>
</div>
