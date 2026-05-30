@extends('admin.layout', ['title' => 'Kilometre Taşı — Düzenle', 'heading' => 'Kilometre Taşı Düzenle'])

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.milestones.update', $milestone->id) }}"
              enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="card-admin space-y-5">

                {{-- Mevcut fotoğraf --}}
                @if ($milestone->image_path)
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Mevcut Fotoğraf</p>
                        <div class="flex items-start gap-4">
                            <img src="{{ Storage::url($milestone->image_path) }}"
                                 alt="{{ $milestone->title }}"
                                 class="h-40 w-auto max-w-xs rounded-xl object-cover shadow">
                            <div class="mt-2 space-y-2">
                                <label class="flex cursor-pointer items-center gap-2 text-sm text-red-500">
                                    <input type="checkbox" name="remove_image" value="1"
                                           class="h-4 w-4 rounded border-gray-300 text-red-500">
                                    Fotoğrafı kaldır
                                </label>
                                <p class="text-xs text-gray-400 dark:text-gray-500">Yeni fotoğraf yüklersen otomatik değişir.</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Yeni fotoğraf --}}
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    <span class="mb-1 block">{{ $milestone->image_path ? 'Fotoğrafı Değiştir' : 'Fotoğraf Yükle' }}</span>
                    <input
                        type="file"
                        name="image"
                        accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-purple-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-purple-700 hover:file:bg-purple-200 dark:text-gray-400 dark:file:bg-purple-900/40 dark:file:text-purple-300"
                    >
                    @error('image')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-gray-400">Maks. 4 MB. JPG, PNG, WebP.</p>
                </label>

                {{-- Başlık --}}
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    <span class="mb-1 block">Başlık <span class="text-red-500">*</span></span>
                    <input
                        type="text"
                        name="title"
                        class="form-input-admin"
                        value="{{ old('title', $milestone->title) }}"
                        required
                        maxlength="255"
                    >
                    @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </label>

                {{-- Açıklama --}}
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    <span class="mb-1 block">Açıklama</span>
                    <textarea
                        name="description"
                        rows="4"
                        class="form-input-admin"
                        maxlength="2000"
                    >{{ old('description', $milestone->description) }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </label>

                {{-- Tarih --}}
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    <span class="mb-1 block">Gerçekleşme Tarihi</span>
                    <input
                        type="datetime-local"
                        name="achieved_at"
                        class="form-input-admin"
                        value="{{ old('achieved_at', $milestone->achieved_at?->format('Y-m-d\TH:i')) }}"
                    >
                    @error('achieved_at')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </label>

            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">
                    Kaydet
                </button>
                <a href="{{ route('admin.milestones.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    İptal
                </a>
                <form method="POST" action="{{ route('admin.milestones.destroy', $milestone->id) }}"
                      class="ml-auto"
                      onsubmit="return confirm('Bu kilometre taşını silmek istediğine emin misin?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400">
                        Sil
                    </button>
                </form>
            </div>
        </form>
    </div>
@endsection
