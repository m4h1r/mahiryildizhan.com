@php
    /** @var \App\Models\Person|null $person */
    $person = $person ?? null;
    $months = collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => \Illuminate\Support\Carbon::create(2000, $m, 1)->translatedFormat('F')]);
    $scopes = config('crm.relationship_scopes');
    $bases = config('crm.relationship_bases');
    $channelTypes = collect(config('crm.channel_types'))->map(fn ($label) => __($label));
    $selectedBases = old('relationship_bases', $person?->relationship_bases ?? []);
    $channelRows = old('channels', $person
        ? $person->channels->map(fn ($c) => ['id' => $c->id, 'type' => $c->type, 'value' => $c->value, 'label' => $c->label, 'is_primary' => $c->is_primary])->all()
        : []);
    $labelClass = 'mb-1 block text-sm font-medium text-ink-soft';
@endphp

@if ($errors->any())
    <x-status-message type="danger" class="mb-6">
        {{ __('Some fields need attention. They are marked below.') }}
    </x-status-message>
@endif

<div class="space-y-10">
    {{-- ============ Identity ============ --}}
    <fieldset class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
        <legend class="mb-4 text-base font-semibold text-ink">{{ __('Identity') }}</legend>

        <label>
            <span class="{{ $labelClass }}">{{ __('Name') }} <span class="text-[var(--color-danger)]" aria-hidden="true">*</span></span>
            <input name="name" class="form-input-admin" value="{{ old('name', $person?->name) }}" required autocomplete="off">
            @error('name') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
        </label>
        <label>
            <span class="{{ $labelClass }}">{{ __('Surname') }} <span class="text-[var(--color-danger)]" aria-hidden="true">*</span></span>
            <input name="surname" class="form-input-admin" value="{{ old('surname', $person?->surname) }}" required autocomplete="off">
            @error('surname') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
        </label>
        <label>
            <span class="{{ $labelClass }}">{{ __('Maiden name') }}</span>
            <input name="second_surname" class="form-input-admin" value="{{ old('second_surname', $person?->second_surname) }}" autocomplete="off">
        </label>
        <label>
            <span class="{{ $labelClass }}">{{ __('Nickname') }}</span>
            <input name="nickname" class="form-input-admin" value="{{ old('nickname', $person?->nickname) }}" autocomplete="off">
        </label>
        <label>
            <span class="{{ $labelClass }}">{{ __('Gender') }}</span>
            <select name="gender_id" class="form-input-admin">
                <option value="">{{ __('Not set') }}</option>
                @foreach ($genders as $gender)
                    <option value="{{ $gender->id }}" @selected((string) old('gender_id', $person?->gender_id) === (string) $gender->id)>{{ __($gender->name) }}</option>
                @endforeach
            </select>
        </label>
        <div class="md:row-span-2">
            @include('admin.people._picture')
        </div>
    </fieldset>

    {{-- ============ Birth & life ============ --}}
    <fieldset class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
        <legend class="mb-1 text-base font-semibold text-ink">{{ __('Birth and life') }}</legend>
        <p class="-mt-1 mb-2 text-sm text-ink-muted md:col-span-2">{{ __('Fill in only what you know — a day and month without a year is fine.') }}</p>

        <div class="md:col-span-2">
            <span class="{{ $labelClass }}" id="birth-label">{{ __('Date of birth') }}</span>
            <div class="grid grid-cols-[minmax(0,0.8fr)_minmax(0,1.4fr)_minmax(0,1fr)] gap-2" role="group" aria-labelledby="birth-label">
                <select name="birth_day" class="form-input-admin" aria-label="{{ __('Day') }}">
                    <option value="">{{ __('Day') }}</option>
                    @foreach (range(1, 31) as $d)
                        <option value="{{ $d }}" @selected((string) old('birth_day', $person?->birth_day) === (string) $d)>{{ $d }}</option>
                    @endforeach
                </select>
                <select name="birth_month" class="form-input-admin" aria-label="{{ __('Month') }}">
                    <option value="">{{ __('Month') }}</option>
                    @foreach ($months as $m => $monthName)
                        <option value="{{ $m }}" @selected((string) old('birth_month', $person?->birth_month) === (string) $m)>{{ $monthName }}</option>
                    @endforeach
                </select>
                <input name="birth_year" type="number" inputmode="numeric" min="1850" max="{{ now()->year + 1 }}" class="form-input-admin" placeholder="{{ __('Year') }}" aria-label="{{ __('Year') }}" value="{{ old('birth_year', $person?->birth_year) }}">
            </div>
            @error('birth_day') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
            @error('birth_year') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
        </div>

        <label>
            <span class="{{ $labelClass }}">{{ __('Birth Place') }}</span>
            <input name="birth_place" class="form-input-admin" value="{{ old('birth_place', $person?->birth_place) }}">
        </label>
        <label>
            <span class="{{ $labelClass }}">{{ __('Date of death') }}</span>
            <input name="deathday" type="date" class="form-input-admin" value="{{ old('deathday', $person?->deathday?->format('Y-m-d')) }}">
            @error('deathday') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
        </label>
        <label>
            <span class="{{ $labelClass }}">{{ __('Death Place') }}</span>
            <input name="death_place" class="form-input-admin" value="{{ old('death_place', $person?->death_place) }}">
        </label>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:col-span-2">
            <label>
                <span class="{{ $labelClass }}">{{ __('Eye Color') }}</span>
                <select name="eye_color_id" class="form-input-admin">
                    <option value="">{{ __('Not set') }}</option>
                    @foreach ($eyeColors as $item)
                        <option value="{{ $item->id }}" @selected((string) old('eye_color_id', $person?->eye_color_id) === (string) $item->id)>{{ __($item->name) }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="{{ $labelClass }}">{{ __('Hair Color') }}</span>
                <select name="hair_color_id" class="form-input-admin">
                    <option value="">{{ __('Not set') }}</option>
                    @foreach ($hairColors as $item)
                        <option value="{{ $item->id }}" @selected((string) old('hair_color_id', $person?->hair_color_id) === (string) $item->id)>{{ __($item->name) }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="{{ $labelClass }}">{{ __('Blood Type') }}</span>
                <select name="blood_type_id" class="form-input-admin">
                    <option value="">{{ __('Not set') }}</option>
                    @foreach ($bloodTypes as $item)
                        <option value="{{ $item->id }}" @selected((string) old('blood_type_id', $person?->blood_type_id) === (string) $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </fieldset>

    {{-- ============ Relationship ============ --}}
    <fieldset class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
        <legend class="mb-1 text-base font-semibold text-ink">{{ __('Relationship') }}</legend>
        <p class="-mt-1 mb-2 text-sm text-ink-muted md:col-span-2">{{ __('How you know this person. A relationship can be personal, professional, or both.') }}</p>

        <div class="md:col-span-2">
            <span class="{{ $labelClass }}" id="scope-label">{{ __('Relationship type') }}</span>
            <div class="glass-well inline-flex flex-wrap gap-1 p-1" role="radiogroup" aria-labelledby="scope-label">
                <label class="cursor-pointer">
                    <input type="radio" name="relationship_scope" value="" class="peer sr-only" @checked(old('relationship_scope', $person?->relationship_scope) === null || old('relationship_scope', $person?->relationship_scope) === '')>
                    <span class="block rounded-[10px] px-3 py-1.5 text-sm font-medium text-ink-muted peer-checked:bg-[var(--glass-strong)] peer-checked:text-ink peer-checked:shadow-sm peer-focus-visible:outline-2 peer-focus-visible:outline-[var(--color-brand)]">{{ __('Not set') }}</span>
                </label>
                @foreach ($scopes as $key => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="relationship_scope" value="{{ $key }}" class="peer sr-only" @checked(old('relationship_scope', $person?->relationship_scope) === $key)>
                        <span class="block rounded-[10px] px-3 py-1.5 text-sm font-medium text-ink-muted peer-checked:bg-[var(--glass-strong)] peer-checked:text-ink peer-checked:shadow-sm peer-focus-visible:outline-2 peer-focus-visible:outline-[var(--color-brand)]">{{ __($label) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="md:col-span-2">
            <span class="{{ $labelClass }}" id="bases-label">{{ __('Relationship bases') }}</span>
            <div class="flex flex-wrap gap-2" role="group" aria-labelledby="bases-label">
                @foreach ($bases as $key => $label)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="relationship_bases[]" value="{{ $key }}" class="peer sr-only" @checked(in_array($key, (array) $selectedBases, true))>
                        <span class="chip peer-checked:border-[oklch(from_var(--brand-primary)_l_c_h_/_0.35)] peer-checked:bg-[var(--brand-wash)] peer-checked:text-[var(--brand-ink)] peer-focus-visible:outline-2 peer-focus-visible:outline-[var(--color-brand)]">{{ __($label) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <label>
            <span class="{{ $labelClass }}">{{ __('In a few words') }}</span>
            <input name="relationship_label" class="form-input-admin" value="{{ old('relationship_label', $person?->relationship_label) }}" placeholder="{{ __('e.g. closest friend, tango partner') }}">
        </label>
        <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1.3fr)] gap-2">
            <label>
                <span class="{{ $labelClass }}">{{ __('Met on') }}</span>
                <input name="met_at" type="date" class="form-input-admin" value="{{ old('met_at', $person?->met_at?->format('Y-m-d')) }}">
            </label>
            <label>
                <span class="{{ $labelClass }}">{{ __('Where or how') }}</span>
                <input name="met_how" class="form-input-admin" value="{{ old('met_how', $person?->met_how) }}">
            </label>
        </div>

        <label>
            <span class="{{ $labelClass }}">{{ __('Company') }}</span>
            <input name="company" class="form-input-admin" value="{{ old('company', $person?->company) }}" autocomplete="off">
        </label>
        <label>
            <span class="{{ $labelClass }}">{{ __('Job title') }}</span>
            <input name="job_title" class="form-input-admin" value="{{ old('job_title', $person?->job_title) }}" autocomplete="off">
        </label>
        <label class="md:col-span-2">
            <span class="{{ $labelClass }}">{{ __('Linked business record') }}</span>
            <select name="stakeholder_id" class="form-input-admin">
                <option value="">{{ __('None') }}</option>
                @foreach ($stakeholders as $stakeholder)
                    <option value="{{ $stakeholder->id }}" @selected((string) old('stakeholder_id', $person?->stakeholder_id) === (string) $stakeholder->id)>
                        {{ $stakeholder->title ?: trim($stakeholder->name.' '.$stakeholder->surname) }}
                    </option>
                @endforeach
            </select>
        </label>

        <div class="grid gap-4 md:col-span-2 md:grid-cols-3">
            @include('admin.people._person-picker', ['field' => 'father_id', 'label' => __('Father'), 'current' => $person?->father, 'excludeId' => $excludeId])
            @include('admin.people._person-picker', ['field' => 'mother_id', 'label' => __('Mother'), 'current' => $person?->mother, 'excludeId' => $excludeId])
            @include('admin.people._person-picker', ['field' => 'partner_id', 'label' => __('Partner'), 'current' => $person?->partner, 'excludeId' => $excludeId])
        </div>
        @foreach (['father_id', 'mother_id', 'partner_id'] as $relationField)
            @error($relationField) <span class="block text-xs text-[var(--color-danger-text)] md:col-span-2">{{ $message }}</span> @enderror
        @endforeach
    </fieldset>

    {{-- ============ Contact ============ --}}
    <fieldset class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
        <legend class="mb-4 text-base font-semibold text-ink">{{ __('Contact and social media') }}</legend>

        <label>
            <span class="{{ $labelClass }}">{{ __('Phone') }}</span>
            <input name="mobile" type="tel" class="form-input-admin" value="{{ old('mobile', $person?->mobile) }}" autocomplete="off">
        </label>
        <label>
            <span class="{{ $labelClass }}">{{ __('Email') }}</span>
            <input name="email" type="email" class="form-input-admin" value="{{ old('email', $person?->email) }}" autocomplete="off">
            @error('email') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
        </label>
        <label class="md:col-span-2">
            <span class="{{ $labelClass }}">{{ __('Address') }}</span>
            <textarea name="address" class="form-input-admin min-h-20" rows="2">{{ old('address', $person?->address) }}</textarea>
        </label>

        <div class="md:col-span-2" x-data="channelRepeater(@js(array_values($channelRows)))">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <span class="text-sm font-medium text-ink-soft">{{ __('Other channels') }}</span>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" class="admin-btn-sm admin-btn-ghost" @click="add('instagram')">+ Instagram</button>
                    <button type="button" class="admin-btn-sm admin-btn-ghost" @click="add('linkedin')">+ LinkedIn</button>
                    <button type="button" class="admin-btn-sm admin-btn-ghost" @click="add('phone')">+ {{ __('Phone') }}</button>
                    <button type="button" class="admin-btn-sm admin-btn-ghost" @click="add('other')">+ {{ __('Other') }}</button>
                </div>
            </div>
            <p x-show="!rows.length" class="glass-well px-4 py-3 text-sm text-ink-muted">{{ __('No extra channels yet. Add social profiles, a second phone or a work e-mail.') }}</p>
            <div class="space-y-2">
                <template x-for="(row, index) in rows" :key="row.key">
                    <div class="glass-well grid grid-cols-[minmax(0,1fr)_auto] items-center gap-2 p-2 sm:grid-cols-[9.5rem_minmax(0,1fr)_minmax(0,0.7fr)_auto_auto]">
                        <input type="hidden" :name="`channels[${index}][id]`" :value="row.id ?? ''">
                        <select :name="`channels[${index}][type]`" x-model="row.type" class="form-input-admin" aria-label="{{ __('Channel type') }}">
                            @foreach ($channelTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="admin-btn-sm admin-btn-danger h-11 w-11 p-0 sm:hidden" @click="remove(index)" aria-label="{{ __('Remove channel') }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
                        </button>
                        <input :name="`channels[${index}][value]`" x-model="row.value" data-channel-value class="form-input-admin col-span-2 sm:col-span-1" placeholder="{{ __('@handle, number or link') }}" aria-label="{{ __('Value') }}" required>
                        <input :name="`channels[${index}][label]`" x-model="row.label" class="form-input-admin col-span-2 sm:col-span-1" placeholder="{{ __('Label (optional)') }}" aria-label="{{ __('Label') }}">
                        <label class="col-span-2 flex items-center gap-2 px-1 text-xs text-ink-soft sm:col-span-1">
                            <input type="checkbox" :name="`channels[${index}][is_primary]`" value="1" x-model="row.is_primary" class="h-4 w-4 accent-[var(--color-brand)]">
                            {{ __('Primary') }}
                        </label>
                        <button type="button" class="admin-btn-sm admin-btn-danger hidden h-11 w-11 p-0 sm:inline-flex" @click="remove(index)" aria-label="{{ __('Remove channel') }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            @error('channels.*') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
        </div>
    </fieldset>

    {{-- ============ Character & interests ============ --}}
    <fieldset class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
        <legend class="mb-1 text-base font-semibold text-ink">{{ __('Character and interests') }}</legend>
        <p class="-mt-1 mb-2 text-sm text-ink-muted md:col-span-2">{{ __('Your own observations. The detailed personality analysis lives in the Analysis tab of the person card.') }}</p>

        <label class="md:col-span-2">
            <span class="{{ $labelClass }}">{{ __('Character') }}</span>
            <textarea name="character_summary" rows="4" class="form-input-admin min-h-28" placeholder="{{ __('Temperament, how they communicate, what matters to them') }}">{{ old('character_summary', $person?->character_summary) }}</textarea>
        </label>
        <div class="md:col-span-2">
            @include('admin.people._chips', ['field' => 'interests', 'label' => __('Curiosities and interests'), 'values' => $person?->interests ?? []])
        </div>
        @include('admin.people._chips', ['field' => 'hobbies', 'label' => __('Hobbies'), 'values' => $person?->hobbies ?? []])
        @include('admin.people._chips', ['field' => 'tendencies', 'label' => __('Tendencies'), 'values' => $person?->tendencies ?? []])
    </fieldset>

    {{-- ============ Notes ============ --}}
    <fieldset class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
        <legend class="mb-4 text-base font-semibold text-ink">{{ __('Notes') }}</legend>
        <label class="md:col-span-2">
            <span class="sr-only">{{ __('Notes') }}</span>
            <textarea name="notes" rows="4" class="form-input-admin min-h-28">{{ old('notes', $person?->notes) }}</textarea>
        </label>
        <label>
            <span class="{{ $labelClass }}">{{ __('Alice key') }}</span>
            <input name="alice_slug" class="form-input-admin font-mono" value="{{ old('alice_slug', $person?->alice_slug) }}" placeholder="eray-civelek" autocomplete="off" spellcheck="false">
            <span class="mt-1 block text-xs text-ink-muted">{{ __('Matches this card with the person record in Alice. Filled in automatically on import.') }}</span>
            @error('alice_slug') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
        </label>
    </fieldset>
</div>

<div class="glass-strong sticky bottom-[calc(var(--admin-bottom-nav-height)+env(safe-area-inset-bottom,0px)+1.5rem)] z-20 mt-8 flex items-center justify-end gap-2 rounded-2xl p-2 md:bottom-3">
    <a href="{{ $person ? route('admin.people.show', $person) : route('admin.people.index') }}" class="admin-btn admin-btn-ghost">{{ __('Cancel') }}</a>
    <button type="submit" class="admin-btn admin-btn-primary">{{ $person ? __('Save changes') : __('Create person') }}</button>
</div>
