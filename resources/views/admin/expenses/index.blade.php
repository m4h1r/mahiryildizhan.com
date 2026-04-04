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

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">Gider Listesi</h2>
                <a href="{{ route('admin.expenses.create') }}" class="admin-btn admin-btn-primary">Yeni Gider</a>
            </div>

            <div class="overflow-x-auto overscroll-x-contain">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th class="hidden sm:table-cell">Paydaş</th>
                            <th class="hidden md:table-cell">Tür</th>
                            <th>Miktar</th>
                            <th class="hidden sm:table-cell">Toplam</th>
                            <th class="hidden lg:table-cell">Durum</th>
                            <th class="text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td>{{ optional($expense->date)->toDateString() }}</td>
                                <td class="hidden sm:table-cell">{{ optional($expense->stakeholder)->title ?: '-' }}</td>
                                <td class="hidden md:table-cell">{{ optional($expense->expenseType)->name ?: '-' }}</td>
                                <td>{{ number_format((float) $expense->price, 2) }} × {{ number_format((float) $expense->quantity, 3) }}</td>
                                <td class="hidden font-medium sm:table-cell">{{ number_format((float) $expense->total, 2) }} {{ optional($expense->currency)->code }}</td>
                                <td class="hidden text-xs lg:table-cell">
                                    {{ $expense->company_expense ? 'Şirket' : 'Kişisel' }} /
                                    {{ $expense->paid_by_others ? 'Başkası' : 'Kendim' }}
                                </td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('admin.expenses.edit', $expense) }}" class="admin-btn-sm admin-btn-ghost">Düzenle</a>

                                        <form method="POST" action="{{ route('admin.expenses.duplicate', $expense) }}">
                                            @csrf
                                            <button type="submit" class="admin-btn-sm admin-btn-ghost">Kopyala</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" onsubmit="return confirm('Bu gideri silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-sm admin-btn-danger">Sil</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Gider bulunamadı.</td>
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
