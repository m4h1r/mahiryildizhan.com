@extends('admin.layout', ['title' => 'Giderler', 'heading' => 'Giderler'])

@section('content')
    @php
        $hasAdvanced = !empty($filters['expense_type_id']) || !empty($filters['currency_id']) || !empty($filters['stakeholder_id']) || !empty($filters['stakeholder_query']) || isset($filters['company_expense']) && $filters['company_expense'] !== '' || isset($filters['paid_by_others']) && $filters['paid_by_others'] !== '';
    @endphp
    <div class="space-y-6" x-data="{
        showAdvanced: {{ $hasAdvanced ? 'true' : 'false' }},
        onDateRange() {
            this.$refs.year.value = '';
            this.$refs.month.value = '';
        },
        onYearMonth() {
            this.$refs.dateFrom.value = '';
            this.$refs.dateTo.value = '';
        }
    }">
        <section class="card-admin">
            <form method="GET" class="space-y-3">
                {{-- Text search --}}
                <input
                    class="form-input-admin"
                    type="search"
                    name="search"
                    placeholder="Açıklama, paydaş adı veya gider türü ara..."
                    value="{{ $filters['search'] ?? '' }}"
                >

                {{-- Primary filters --}}
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <input class="form-input-admin" type="date" name="date_from" x-ref="dateFrom" value="{{ $filters['date_from'] ?? '' }}" @change="onDateRange()">
                    <input class="form-input-admin" type="date" name="date_to" x-ref="dateTo" value="{{ $filters['date_to'] ?? '' }}" @change="onDateRange()">
                    <input class="form-input-admin" type="number" name="year" x-ref="year" placeholder="Yıl" value="{{ $filters['year'] ?? '' }}" @input="onYearMonth()">
                    <input class="form-input-admin" type="number" min="1" max="12" name="month" x-ref="month" placeholder="Ay" value="{{ $filters['month'] ?? '' }}" @input="onYearMonth()">
                </div>

                {{-- Advanced filters (toggle) --}}
                <div x-show="showAdvanced" x-transition class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <select name="expense_type_id" class="form-input-admin" :disabled="!showAdvanced">
                        <option value="">Tüm Tipler</option>
                        @foreach ($expenseTypes as $expenseType)
                            <option value="{{ $expenseType->id }}" @selected((string) ($filters['expense_type_id'] ?? '') === (string) $expenseType->id)>{{ $expenseType->name }}</option>
                        @endforeach
                    </select>

                    <select name="currency_id" class="form-input-admin" :disabled="!showAdvanced">
                        <option value="">Tüm Para Birimleri</option>
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency->id }}" @selected((string) ($filters['currency_id'] ?? '') === (string) $currency->id)>{{ $currency->code }}</option>
                        @endforeach
                    </select>

                    <select name="stakeholder_id" class="form-input-admin" :disabled="!showAdvanced">
                        <option value="">Tüm Paydaşlar</option>
                        @foreach ($stakeholders as $stakeholder)
                            <option value="{{ $stakeholder->id }}" @selected((string) ($filters['stakeholder_id'] ?? '') === (string) $stakeholder->id)>
                                {{ $stakeholder->title ?: trim(($stakeholder->name ?? '').' '.($stakeholder->surname ?? '')) }}
                            </option>
                        @endforeach
                    </select>

                    <input class="form-input-admin" name="stakeholder_query" placeholder="Paydaş adı veya VKN ara" value="{{ $filters['stakeholder_query'] ?? '' }}" :disabled="!showAdvanced">

                    <select name="company_expense" class="form-input-admin" :disabled="!showAdvanced">
                        <option value="">Tüm Şirket Durumları</option>
                        <option value="1" @selected(($filters['company_expense'] ?? '') === '1')>Şirket</option>
                        <option value="0" @selected(($filters['company_expense'] ?? '') === '0')>Kişisel</option>
                    </select>

                    <select name="paid_by_others" class="form-input-admin" :disabled="!showAdvanced">
                        <option value="">Tüm Ödeme Durumları</option>
                        <option value="1" @selected(($filters['paid_by_others'] ?? '') === '1')>Başkası ödedi</option>
                        <option value="0" @selected(($filters['paid_by_others'] ?? '') === '0')>Ben ödedim</option>
                    </select>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="admin-btn admin-btn-primary">Filtrele</button>
                    <a href="{{ route('admin.expenses.index') }}" class="admin-btn admin-btn-ghost">Sıfırla</a>
                    <button type="button" @click="showAdvanced = !showAdvanced" class="admin-btn admin-btn-ghost">
                        <span x-text="showAdvanced ? 'Filtreleri Gizle' : 'Daha Fazla Filtre'"></span>
                        <svg class="h-3.5 w-3.5 transition-transform duration-150" :class="showAdvanced ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                    </button>
                </div>
            </form>
        </section>

        <section class="admin-table-shell" x-data="{
            showExport: false,
            exportDateFrom: '{{ now()->startOfYear()->toDateString() }}',
            exportDateTo: '{{ now()->toDateString() }}',
        }">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">Gider Listesi</h2>
                <div class="flex gap-2">
                    <button type="button" @click="showExport = true" class="admin-btn admin-btn-ghost">Export</button>
                    <a href="{{ route('admin.expenses.create') }}" class="admin-btn admin-btn-primary">Yeni Gider</a>
                </div>
            </div>

            {{-- Export Modal --}}
            <div x-show="showExport" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="showExport = false">
                <form method="GET" action="{{ route('admin.expenses.export') }}" target="_blank"
                    class="card-admin w-full max-w-sm space-y-4 shadow-xl"
                    @click.stop>
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold">Gider Export</h2>
                        <button type="button" @click="showExport = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="Kapat">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="label-admin">Başlangıç Tarihi</label>
                            <input type="date" name="date_from" class="form-input-admin" x-model="exportDateFrom">
                        </div>
                        <div>
                            <label class="label-admin">Bitiş Tarihi</label>
                            <input type="date" name="date_to" class="form-input-admin" x-model="exportDateTo">
                        </div>
                    </div>

                    <div>
                        <label class="label-admin">Şirket Gideri</label>
                        <select name="company_expense" class="form-input-admin">
                            <option value="">Tümü</option>
                            <option value="1" selected>Şirket</option>
                            <option value="0">Kişisel</option>
                        </select>
                    </div>

                    <div>
                        <label class="label-admin">Para Birimi</label>
                        <select name="currency_id" class="form-input-admin">
                            <option value="">Tümü</option>
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label-admin">Gider Türü</label>
                        <select name="expense_type_id" class="form-input-admin">
                            <option value="">Tümü</option>
                            @foreach ($expenseTypes as $expenseType)
                                <option value="{{ $expenseType->id }}">{{ $expenseType->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label-admin">Paydaş</label>
                        <select name="stakeholder_id" class="form-input-admin">
                            <option value="">Tümü</option>
                            @foreach ($stakeholders as $stakeholder)
                                <option value="{{ $stakeholder->id }}">{{ $stakeholder->title ?: trim(($stakeholder->name ?? '').' '.($stakeholder->surname ?? '')) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="showExport = false" class="admin-btn admin-btn-ghost">İptal</button>
                        <button type="submit" class="admin-btn admin-btn-primary">CSV İndir</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="w-10 text-xs text-gray-400 dark:text-gray-500">#</th>
                            <th>Tarih</th>
                            <th class="hidden sm:table-cell">Paydaş</th>
                            <th class="hidden md:table-cell">Tür</th>
                            <th>Toplam</th>
                            <th class="text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td class="text-xs text-gray-400 dark:text-gray-500">{{ $expense->id }}</td>
                                <td>{{ optional($expense->date)->toDateString() }}</td>
                                <td class="hidden sm:table-cell">
                                    <div class="flex items-center gap-1.5">
                                        @if($expense->company_expense)
                                            <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">C</span>
                                        @else
                                            <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded bg-orange-100 text-xs font-bold text-orange-700 dark:bg-orange-900/50 dark:text-orange-300">P</span>
                                        @endif
                                        {{ optional($expense->stakeholder)->title ?: '-' }}
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">{{ optional($expense->expenseType)->name ?: '-' }}</td>
                                <td class="font-medium">{{ number_format((float) $expense->total, 2) }} {{ optional($expense->currency)->code }}</td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.expenses.edit', $expense) }}" class="admin-btn-sm admin-btn-ghost" title="Düzenle" aria-label="Düzenle">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.expenses.duplicate', $expense) }}">
                                            @csrf
                                            <button type="submit" class="admin-btn-sm admin-btn-ghost" title="Kopyala" aria-label="Kopyala">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.676a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" onsubmit="return confirm('Bu gideri silmek istediğinize emin misiniz?');">
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
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Gider bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                {{ $expenses->links() }}
            </div>
        </section>
    </div>
@endsection
