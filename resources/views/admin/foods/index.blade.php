@extends('admin.layout', ['title' => 'Besinler', 'heading' => 'Besinler'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="flex flex-wrap gap-2">
                <input class="form-input-admin flex-1" type="search" name="search" placeholder="Besin adı ara..." value="{{ $filters['search'] ?? '' }}">
                <button type="submit" class="admin-btn admin-btn-primary">Filtrele</button>
                <a href="{{ route('admin.foods.index') }}" class="admin-btn admin-btn-ghost">Sıfırla</a>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">Besin Listesi</h2>
                <a href="{{ route('admin.foods.create') }}" class="admin-btn admin-btn-primary">Yeni Besin</a>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="w-10 text-xs text-gray-400 dark:text-gray-500">#</th>
                            <th>Ad</th>
                            <th>Kalori (100g)</th>
                            <th class="hidden sm:table-cell">Karb. (g)</th>
                            <th class="hidden sm:table-cell">Şeker (g)</th>
                            <th class="hidden sm:table-cell">Yağ (g)</th>
                            <th class="hidden md:table-cell">Ölçü</th>
                            <th class="text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($foods as $food)
                            <tr>
                                <td class="text-xs text-gray-400 dark:text-gray-500">{{ $food->id }}</td>
                                <td class="font-medium">{{ $food->name }}</td>
                                <td>{{ number_format($food->calories_per_100g) }} kcal</td>
                                <td class="hidden sm:table-cell">{{ number_format((float) $food->carbs_per_100g, 1) }}</td>
                                <td class="hidden sm:table-cell">{{ number_format((float) $food->sugar_per_100g, 1) }}</td>
                                <td class="hidden sm:table-cell">{{ number_format((float) $food->fat_per_100g, 1) }}</td>
                                <td class="hidden md:table-cell">
                                    {{ $food->unit_type === 'piece' ? 'Adet ('.number_format((float) $food->grams_per_unit, 0).'g)' : 'Gram' }}
                                </td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.foods.edit', $food) }}" class="admin-btn-sm admin-btn-ghost" title="Düzenle" aria-label="Düzenle">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.foods.destroy', $food) }}" data-confirm="Bu besini silmek istediğinize emin misiniz?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger" title="Sil" aria-label="Sil">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Besin bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                {{ $foods->links() }}
            </div>
        </section>
    </div>
@endsection
