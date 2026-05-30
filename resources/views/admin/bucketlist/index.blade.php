@extends('admin.layout', ['title' => 'Bucket List', 'heading' => 'Bucket List'])

@section('content')
    <div class="space-y-6">

        {{-- İstatistik header --}}
        <section class="card-admin border border-purple-200/70 bg-gradient-to-br from-purple-50 to-fuchsia-50 dark:border-purple-800/60 dark:from-purple-950/30 dark:to-fuchsia-950/30">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bucket List İlerlemesi</p>
                    <p class="mt-1 text-3xl font-extrabold text-purple-700 dark:text-purple-300">
                        {{ $completed }} / {{ $total }}
                        <span class="text-base font-normal text-gray-500 dark:text-gray-400">tamamlandı</span>
                    </p>
                </div>
                <span class="text-4xl font-extrabold text-purple-600 dark:text-purple-400">%{{ $percentage }}</span>
            </div>
            <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-purple-100 dark:bg-purple-900/40">
                <div
                    class="h-full rounded-full bg-gradient-to-r from-purple-500 to-fuchsia-500 transition-all duration-700"
                    style="width: {{ $percentage }}%"
                ></div>
            </div>
        </section>

        {{-- Satın Alınacaklar --}}
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                    Satın Alınacaklar
                    <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">{{ $purchaseItems->count() }}</span>
                </h2>
                <a href="{{ route('admin.purchase-items.create') }}?is_bucketlist=1" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">+ Ekle</a>
            </div>

            @if ($purchaseItems->isEmpty())
                <p class="rounded-xl border border-dashed border-gray-300 p-5 text-center text-sm text-gray-400 dark:border-gray-700">
                    Bucket list'e eklenmiş alım yok.
                </p>
            @else
                <div class="card-admin overflow-hidden p-0">
                    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($purchaseItems as $item)
                            <li class="flex items-center gap-3 px-4 py-3 transition hover:bg-gray-50/60 dark:hover:bg-gray-900/30 {{ $item->is_completed ? 'opacity-60' : '' }}">
                                <form method="POST" action="{{ route('admin.purchase-items.toggle-complete', $item->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition {{ $item->is_completed ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 hover:border-emerald-400 dark:border-gray-600' }}"
                                    >
                                        @if ($item->is_completed)
                                            <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100 {{ $item->is_completed ? 'line-through' : '' }}">
                                        {{ $item->title }}
                                    </p>
                                    @if ($item->description)
                                        <p class="mt-0.5 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0 flex items-center gap-3 text-xs text-gray-400">
                                    @if ($item->cost_try)
                                        <span>₺{{ number_format((float)$item->cost_try, 0) }}</span>
                                    @endif
                                    @if ($item->time_cost_hours)
                                        <span>{{ number_format((float)$item->time_cost_hours, 1) }} sa</span>
                                    @endif
                                </div>
                                <a href="{{ route('admin.purchase-items.edit', $item->id) }}" class="shrink-0 text-xs text-blue-600 hover:underline dark:text-blue-400">Düzenle</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- Yapılacaklar --}}
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                    Yapılacaklar
                    <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">{{ $todoItems->count() }}</span>
                </h2>
                <a href="{{ route('admin.todo-items.create') }}?is_bucketlist=1" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">+ Ekle</a>
            </div>

            @if ($todoItems->isEmpty())
                <p class="rounded-xl border border-dashed border-gray-300 p-5 text-center text-sm text-gray-400 dark:border-gray-700">
                    Bucket list'e eklenmiş görev yok.
                </p>
            @else
                <div class="card-admin overflow-hidden p-0">
                    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($todoItems as $item)
                            <li class="flex items-center gap-3 px-4 py-3 transition hover:bg-gray-50/60 dark:hover:bg-gray-900/30 {{ $item->is_completed ? 'opacity-60' : '' }}">
                                <form method="POST" action="{{ route('admin.todo-items.toggle-complete', $item->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition {{ $item->is_completed ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 hover:border-emerald-400 dark:border-gray-600' }}"
                                    >
                                        @if ($item->is_completed)
                                            <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100 {{ $item->is_completed ? 'line-through' : '' }}">
                                        {{ $item->title }}
                                    </p>
                                    @if ($item->description)
                                        <p class="mt-0.5 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0 flex items-center gap-3 text-xs text-gray-400">
                                    @if ($item->due_date)
                                        <span class="rounded-full px-2 py-0.5 {{ $item->due_date->isPast() && !$item->is_completed ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-gray-100 dark:bg-gray-800' }}">
                                            {{ $item->due_date->format('d M Y') }}
                                        </span>
                                    @endif
                                    @if ($item->cost_try)
                                        <span>₺{{ number_format((float)$item->cost_try, 0) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('admin.todo-items.edit', $item->id) }}" class="shrink-0 text-xs text-blue-600 hover:underline dark:text-blue-400">Düzenle</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

    </div>
@endsection
