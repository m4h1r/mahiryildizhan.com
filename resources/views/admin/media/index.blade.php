@extends('admin.layout', ['title' => 'Media', 'heading' => 'Media'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input class="form-input-admin sm:col-span-2" name="q" placeholder="{{ __('Search media...') }}" value="{{ $filters['q'] ?? '' }}">
                <select name="type" class="form-input-admin">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="1" @selected((string) ($filters['type'] ?? '') === '1')>{{ __('Images') }}</option>
                    <option value="2" @selected((string) ($filters['type'] ?? '') === '2')>{{ __('Documents') }}</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="admin-btn admin-btn-primary flex-1">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.media.index') }}" class="admin-btn admin-btn-ghost flex-1 text-center">{{ __('Reset') }}</a>
                </div>
            </form>
        </section>

        <section class="card-admin p-4 md:p-6">
            <h2 class="mb-3 text-sm font-semibold">{{ __('Upload New Media') }}</h2>
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="grid gap-3 md:grid-cols-3">
                @csrf
                <input type="file" name="file" required class="form-input-admin md:col-span-3">
                <input type="text" name="alt" class="form-input-admin" placeholder="{{ __('Alt text (optional)') }}">
                <input type="text" name="caption" class="form-input-admin" placeholder="{{ __('Caption (optional)') }}">
                <button type="submit" class="admin-btn admin-btn-primary">{{ __('Upload') }}</button>
            </form>
            @error('file')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            @error('alt')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            @error('caption')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </section>

        <section class="card-admin overflow-hidden">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold">{{ __('Media Library') }}</h2>
            </div>

            <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($mediaItems as $item)
                    <article class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                        <img src="{{ $item->url('thumbnail') }}" alt="{{ $item->alt ?: $item->filename }}" class="aspect-video w-full rounded-lg object-cover">
                        <p class="mt-2 truncate text-sm font-medium">{{ $item->filename }}</p>
                        <p class="text-xs text-gray-500">{{ strtoupper($item->mime_type) }}</p>
                        <p class="text-xs text-gray-500">{{ strtoupper($item->extension ?: '-') }} | {{ (int) ($item->width ?? 0) }}x{{ (int) ($item->height ?? 0) }}</p>
                        <p class="text-xs text-gray-500">{{ number_format(($item->size ?? 0) / 1024, 1) }} KB</p>
                        <p class="text-xs text-gray-500">{{ __('Used as cover:') }} {{ number_format((int) ($item->cover_posts_count ?? 0)) }}</p>
                        @if (! empty($item->variant_paths))
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach (array_keys($item->variant_paths) as $variant)
                                    <span class="rounded-full border border-gray-300 px-2 py-0.5 text-[10px] font-medium uppercase text-gray-600 dark:border-gray-700 dark:text-gray-300">{{ $variant }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="mt-2 flex justify-end">
                            <form method="POST" action="{{ route('admin.media.destroy', $item) }}" data-confirm="{{ __('Delete this media item?') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn-sm admin-btn-danger disabled:cursor-not-allowed disabled:opacity-50" @disabled(($item->cover_posts_count ?? 0) > 0)>{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No media found.') }}</p>
                @endforelse
            </div>

            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                {{ $mediaItems->links() }}
            </div>
        </section>
    </div>
@endsection
