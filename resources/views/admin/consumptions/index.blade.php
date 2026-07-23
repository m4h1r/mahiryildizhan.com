@extends('admin.layout', ['title' => 'Tüketimler', 'heading' => 'Tüketimler'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin">
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input class="form-input-admin" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                <input class="form-input-admin" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                <select name="food_id" class="form-input-admin">
                    <option value="">Tüm Besinler</option>
                    @foreach ($foods as $food)
                        <option value="{{ $food->id }}" @selected((string) ($filters['food_id'] ?? '') === (string) $food->id)>{{ $food->name }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="admin-btn admin-btn-primary">Filtrele</button>
                    <a href="{{ route('admin.consumptions.index') }}" class="admin-btn admin-btn-ghost">Sıfırla</a>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">Tüketim Listesi</h2>
                <a href="{{ route('admin.consumptions.create') }}" class="admin-btn admin-btn-primary">Yeni Tüketim</a>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="w-10 text-xs text-gray-400 dark:text-gray-500">#</th>
                            <th>Tarih</th>
                            <th>Besin</th>
                            <th>Miktar</th>
                            <th class="hidden sm:table-cell">Kalori</th>
                            <th class="text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($consumptions as $consumption)
                            <tr>
                                <td class="text-xs text-gray-400 dark:text-gray-500">{{ $consumption->id }}</td>
                                <td>{{ optional($consumption->consumed_on)->toDateString() }}</td>
                                <td class="font-medium">{{ optional($consumption->food)->name }}</td>
                                <td>{{ number_format((float) $consumption->quantity, 2) }} {{ optional($consumption->food)->unit_type === 'piece' ? 'adet' : 'g' }}</td>
                                <td class="hidden sm:table-cell">{{ number_format($consumption->calories(), 0) }} kcal</td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.consumptions.edit', $consumption) }}" class="admin-btn-sm admin-btn-ghost" title="Düzenle" aria-label="Düzenle">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.consumptions.destroy', $consumption) }}" data-confirm="Bu tüketim kaydını silmek istediğinize emin misiniz?">
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
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Tüketim kaydı bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                {{ $consumptions->links() }}
            </div>
        </section>
    </div>
@endsection
