@extends('admin.layout', ['title' => 'Yapılacaklar', 'heading' => 'Yapılacaklar'])

@section('content')
    <div class="space-y-5">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-1 rounded-xl glass-well p-1 dark:border-white/10 dark:bg-white/[0.06]">
                @foreach (['pending' => 'Bekleyen', 'due' => 'Bugün / Gecikmiş', 'completed' => 'Tamamlandı', 'archived' => 'Arşiv', 'bucketlist' => 'Bucket List', 'yearly_goal' => 'Yıllık Hedefler', 'all' => 'Tümü'] as $key => $label)
                    <a
                        href="{{ route('admin.todo-items.index', ['filter' => $key, 'q' => request('q')]) }}"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ $filter === $key ? 'bg-[var(--glass-strong)] shadow-sm text-ink' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                    >{{ $label }}</a>
                @endforeach
            </div>

            <a href="{{ route('admin.todo-items.create') }}" class="admin-btn admin-btn-primary">
                + Yeni Görev
            </a>
        </div>

        <div class="rounded-2xl glass-well p-3">
            <x-global-search
                action="{{ route('admin.todo-items.index') }}"
                value="{{ request('q', '') }}"
                placeholder="{{ __('Görevlerde ara...') }}"
                wrapper-class="flex w-full items-center gap-2"
                input-class="w-full rounded-full border border-glass bg-[var(--glass-hover)] px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-white/10 dark:bg-black/20 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-gray-200 dark:focus:ring-gray-800"
                button-class="admin-btn admin-btn-primary rounded-full"
            />
        </div>

        <div class="card-admin overflow-hidden p-0">
            @if ($items->isEmpty())
                <p class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ $filter === 'all' ? 'Henüz görev eklenmedi.' : 'Bu filtrede kayıt yok.' }}
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-glass bg-[var(--glass-hover)]">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Görev</th>
                                <th class="hidden px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300 sm:table-cell">Tarih</th>
                                <th class="hidden px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300 md:table-cell">Urgency</th>
                                <th class="hidden px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300 md:table-cell">Importance</th>
                                <th class="hidden px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300 md:table-cell">Maliyet</th>
                                <th class="hidden px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300 lg:table-cell">Süre</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Durum</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--glass-line)]">
                            @foreach ($items as $item)
                                @php
                                    $isOverdue = $item->due_date && !$item->is_completed && $item->due_date->isPast();
                                    $isToday   = $item->due_date && $item->due_date->isToday();
                                    $ageDays   = (int) $item->created_at->diffInDays(now());
                                @endphp
                                <tr class="transition {{ !$item->is_completed && $ageDays >= 100 ? 'bg-red-50/70 dark:bg-red-900/10 hover:bg-red-50 dark:hover:bg-red-900/20' : (!$item->is_completed && $ageDays >= 50 ? 'bg-yellow-50/70 dark:bg-yellow-900/10 hover:bg-yellow-50 dark:hover:bg-yellow-900/20' : 'hover:bg-[var(--glass-hover)] dark:hover:bg-white/5') }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold tabular-nums {{ $ageDays >= 100 ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : ($ageDays >= 50 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400') }}">{{ $ageDays }}g</span>
                                            @if ($item->is_bucketlist)
                                                <span class="shrink-0 rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-semibold text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">★ BL</span>
                                            @endif
                                            @if ($item->yearly_goal !== 'NA')
                                                <span class="shrink-0 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">🎯 {{ $item->yearly_goal }}</span>
                                            @endif
                                            <span class="font-medium text-gray-800 dark:text-gray-100 {{ $item->is_completed ? 'line-through opacity-60' : '' }}">
                                                {{ $item->title }}
                                            </span>
                                        </div>
                                        @if ($item->description)
                                            <p class="mt-0.5 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
                                        @endif
                                    </td>
                                    <td class="hidden px-4 py-3 text-center sm:table-cell">
                                        @if ($item->due_date)
                                            <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                                {{ $isToday ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300' : ($isOverdue ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300') }}">
                                                {{ $item->due_date->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="hidden px-4 py-3 text-right text-gray-600 dark:text-gray-300 md:table-cell">
                                        {{ $item->urgency ?? 'NA' }}
                                    </td>
                                    <td class="hidden px-4 py-3 text-right text-gray-600 dark:text-gray-300 md:table-cell">
                                        {{ $item->importance ?? 'NA' }}
                                    </td>
                                    <td class="hidden px-4 py-3 text-right text-gray-600 dark:text-gray-300 md:table-cell">
                                        {{ $item->cost_try ? '₺'.number_format((float)$item->cost_try, 2) : '—' }}
                                    </td>
                                    <td class="hidden px-4 py-3 text-right text-gray-600 dark:text-gray-300 lg:table-cell">
                                        {{ $item->time_cost_hours ? number_format((float)$item->time_cost_hours, 1).' sa' : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST" action="{{ route('admin.todo-items.toggle-complete', $item->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="inline-flex h-6 w-6 items-center justify-center rounded-full border-2 transition {{ $item->is_completed ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-[var(--field-line)] hover:border-emerald-400 dark:border-white/10' }}"
                                                title="{{ $item->is_completed ? 'Geri al' : 'Tamamlandı işaretle' }}"
                                            >
                                                @if ($item->is_completed)
                                                    <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.todo-items.edit', $item->id) }}" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">Düzenle</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($items->hasPages())
                    <div class="border-t border-glass px-4 py-3 dark:border-white/10">
                        {{ $items->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
@endsection
