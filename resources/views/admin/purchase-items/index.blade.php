@extends('admin.layout', ['title' => 'Satın Alınacaklar', 'heading' => 'Satın Alınacaklar'])

@section('content')
    <div class="space-y-5">

        <div class="flex flex-wrap items-center justify-between gap-3">
            {{-- Filtreler --}}
            <div class="flex flex-wrap gap-1 rounded-xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-gray-800">
                @foreach (['all' => 'Tümü', 'pending' => 'Bekleyen', 'completed' => 'Satın Alındı', 'bucketlist' => 'Bucket List'] as $key => $label)
                    <a
                        href="{{ route('admin.purchase-items.index', ['filter' => $key]) }}"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ $filter === $key ? 'bg-white shadow text-gray-900 dark:bg-gray-700 dark:text-gray-100' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                    >{{ $label }}</a>
                @endforeach
            </div>

            <a href="{{ route('admin.purchase-items.create') }}" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">
                + Yeni Alım
            </a>
        </div>

        <div class="card-admin overflow-hidden p-0">
            @if ($items->isEmpty())
                <p class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ $filter === 'all' ? 'Henüz alım eklenmedi.' : 'Bu filtrede kayıt yok.' }}
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Başlık</th>
                                <th class="hidden px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300 sm:table-cell">Maliyet</th>
                                <th class="hidden px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300 md:table-cell">Süre</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Durum</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($items as $item)
                                <tr class="transition hover:bg-gray-50/60 dark:hover:bg-gray-900/30">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            @if ($item->is_bucketlist)
                                                <span class="shrink-0 rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-semibold text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">★ BL</span>
                                            @endif
                                            <span class="font-medium text-gray-800 dark:text-gray-100 {{ $item->is_completed ? 'line-through opacity-60' : '' }}">
                                                {{ $item->title }}
                                            </span>
                                        </div>
                                        @if ($item->description)
                                            <p class="mt-0.5 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
                                        @endif
                                    </td>
                                    <td class="hidden px-4 py-3 text-right text-gray-600 dark:text-gray-300 sm:table-cell">
                                        {{ $item->cost_try ? '₺'.number_format((float)$item->cost_try, 2) : '—' }}
                                    </td>
                                    <td class="hidden px-4 py-3 text-right text-gray-600 dark:text-gray-300 md:table-cell">
                                        {{ $item->time_cost_hours ? number_format((float)$item->time_cost_hours, 1).' sa' : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST" action="{{ route('admin.purchase-items.toggle-complete', $item->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="inline-flex h-6 w-6 items-center justify-center rounded-full border-2 transition {{ $item->is_completed ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 hover:border-emerald-400 dark:border-gray-600' }}"
                                                title="{{ $item->is_completed ? 'Geri al' : 'Tamamlandı işaretle' }}"
                                            >
                                                @if ($item->is_completed)
                                                    <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.purchase-items.edit', $item->id) }}" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">Düzenle</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($items->hasPages())
                    <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                        {{ $items->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
@endsection
