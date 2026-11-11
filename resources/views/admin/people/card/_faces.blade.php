{{-- Face samples for recognition. Biometric data: private disk, encrypted embeddings, admin-only streaming. --}}
<x-status-message type="info">
    {{ __('Face images and recognition data are special-category personal data under KVKK. They are stored privately, never published, and only you and Alice can read them.') }}
</x-status-message>

<div class="grid gap-5 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
    <article class="card-admin min-w-0">
        <h3 class="text-base font-semibold text-ink">{{ __('Samples') }}</h3>
        @if ($person->faces->isEmpty())
            <div class="mt-3">@include('admin.people.card._empty', ['message' => __('No face samples yet. Upload a clear, front-facing photo; add the embedding JSON if you already have one.')])</div>
        @else
            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($person->faces as $face)
                    <figure class="glass-well overflow-hidden p-0">
                        @if ($face->image_path)
                            <img src="{{ route('admin.people.faces.image', [$person, $face]) }}" alt="{{ __('Face sample') }} #{{ $face->id }}" class="aspect-square w-full object-cover" loading="lazy">
                        @else
                            <div class="flex aspect-square w-full items-center justify-center text-xs text-ink-muted">{{ __('Embedding only') }}</div>
                        @endif
                        <figcaption class="space-y-1 p-2.5 text-xs text-ink-soft">
                            <div class="flex flex-wrap items-center gap-1">
                                @if ($face->is_primary) <span class="chip chip-brand min-h-5 px-1.5 text-[10px]">{{ __('Primary') }}</span> @endif
                                <span class="chip min-h-5 px-1.5 text-[10px]">{{ __(ucfirst($face->source)) }}</span>
                            </div>
                            @if ($face->hasEmbedding())
                                <p class="truncate" title="{{ $face->embedding_model }}">{{ $face->embedding_model ?: __('Unknown model') }} · {{ $face->embedding_dim }}d</p>
                                <p class="truncate font-mono text-[10.5px] text-ink-muted">[{{ collect(array_slice((array) $face->embedding, 0, 3))->map(fn ($v) => number_format((float) $v, 3))->implode(', ') }}, …]</p>
                            @else
                                <p class="text-ink-muted">{{ __('No embedding') }}</p>
                            @endif
                            @if ($face->quality !== null)
                                <p>{{ __('Quality') }} %{{ (int) round($face->quality * 100) }}</p>
                            @endif
                            <div class="flex gap-1.5 pt-1">
                                @unless ($face->is_primary)
                                    <form method="POST" action="{{ route('admin.people.faces.primary', [$person, $face]) }}">
                                        @csrf
                                        <button type="submit" class="admin-btn-sm admin-btn-ghost">{{ __('Make primary') }}</button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('admin.people.faces.destroy', [$person, $face]) }}" data-confirm="{{ __('Delete this face sample? The image file is removed right away.') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn-sm admin-btn-danger" aria-label="{{ __('Delete face sample') }} #{{ $face->id }}">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </article>

    <article class="card-admin min-w-0">
        <h3 class="text-base font-semibold text-ink">{{ __('Add a sample') }}</h3>
        <form method="POST" action="{{ route('admin.people.faces.store', $person) }}" enctype="multipart/form-data" class="mt-3 space-y-4">
            @csrf
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-ink-soft">{{ __('Face photo') }}</span>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/heic" class="form-input-admin">
                <span class="mt-1 block text-xs text-ink-muted">{{ __('Location and camera details (EXIF) are removed when the photo is saved.') }}</span>
                @error('image') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-ink-soft">{{ __('Recognition data (JSON)') }}</span>
                <textarea name="embedding_json" rows="5" class="form-input-admin font-mono text-xs" spellcheck="false" placeholder='{"embedding": [0.012, -0.044, …], "embedding_model": "arcface-r100", "quality": 0.92}'>{{ old('embedding_json') }}</textarea>
                @error('embedding_json') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
                @error('embedding') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
                @error('embedding_model') <span class="mt-1 block text-xs text-[var(--color-danger-text)]">{{ $message }}</span> @enderror
            </label>
            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input type="checkbox" name="is_primary" value="1" class="h-4 w-4 accent-[var(--color-brand)]">
                {{ __('Make this the primary face') }}
            </label>
            <button type="submit" class="admin-btn admin-btn-primary w-full">{{ __('Save sample') }}</button>
        </form>
    </article>
</div>
