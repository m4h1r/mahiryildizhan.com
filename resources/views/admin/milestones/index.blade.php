@extends('admin.layout', ['title' => 'Kilometre Taşları', 'heading' => 'Kilometre Taşları'])

@section('content')

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if ($milestones->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 py-20 text-center dark:border-gray-700">
            <svg class="mb-4 h-12 w-12 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 0 1 2-2h6.5L21 13l-5.5 5.5H3v-1.5Z"/>
            </svg>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Henüz kilometre taşı yok</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Bir bucket list maddesini tamamladığında buraya otomatik eklenir.</p>
        </div>
    @else

        {{-- Timeline --}}
        <div class="relative">
            {{-- Dikey çizgi --}}
            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-purple-300 via-purple-200 to-transparent dark:from-purple-700 dark:via-purple-800"></div>

            <div class="space-y-8">
                @foreach ($milestones as $milestone)
                    @php
                        $type = $milestone->milestoneable_type
                            ? class_basename($milestone->milestoneable_type)
                            : null;
                        $typeLabel = match($type) {
                            'PurchaseItem' => 'Satın Alındı',
                            'TodoItem'     => 'Tamamlandı',
                            default        => 'Kilometre Taşı',
                        };
                        $typeColor = match($type) {
                            'PurchaseItem' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                            'TodoItem'     => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                            default        => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                        };
                    @endphp

                    <div class="relative flex gap-5">
                        {{-- Nokta --}}
                        <div class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white ring-4 ring-purple-200 dark:bg-gray-900 dark:ring-purple-800">
                            @if ($milestone->image_path)
                                <img src="{{ Storage::url($milestone->image_path) }}"
                                     class="h-12 w-12 rounded-full object-cover"
                                     alt="{{ $milestone->title }}">
                            @else
                                <svg class="h-5 w-5 text-purple-500 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 0 1 2-2h6.5L21 13l-5.5 5.5H3v-1.5Z"/>
                                </svg>
                            @endif
                        </div>

                        {{-- Kart --}}
                        <article class="flex-1 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800/60">
                            @if ($milestone->image_path)
                                <div class="aspect-video w-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                                    <img src="{{ Storage::url($milestone->image_path) }}"
                                         alt="{{ $milestone->title }}"
                                         class="h-full w-full object-cover">
                                </div>
                            @endif

                            <div class="p-4">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $typeColor }}">
                                        {{ $typeLabel }}
                                    </span>
                                    @if ($milestone->achieved_at)
                                        <time class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $milestone->achieved_at->translatedFormat('d F Y') }}
                                        </time>
                                    @endif
                                </div>

                                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $milestone->title }}</h2>

                                @if ($milestone->description)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $milestone->description }}</p>
                                @endif

                                <div class="mt-3 flex items-center gap-3">
                                    <a href="{{ route('admin.milestones.edit', $milestone->id) }}"
                                       class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">
                                        Düzenle / Fotoğraf ekle
                                    </a>
                                    <form method="POST" action="{{ route('admin.milestones.destroy', $milestone->id) }}"
                                          onsubmit="return confirm('Bu kilometre taşını silmek istediğine emin misin?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400">
                                            Sil
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-8">
            {{ $milestones->links() }}
        </div>

    @endif

@endsection
