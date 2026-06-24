@extends('admin.layout', ['title' => 'Gelirler', 'heading' => 'Gelirler'])

@section('content')
    @php
        $sortDir  = fn ($col) => $sort === $col ? ($direction === 'asc' ? 'desc' : 'asc') : 'asc';
        $sortIcon = function ($col) use ($sort, $direction) {
            if ($sort !== $col) return '<span class="text-gray-300 dark:text-gray-600">↕</span>';
            return $direction === 'asc' ? '↑' : '↓';
        };
        $sortUrl  = fn ($col) => route('admin.incomes.index', array_merge(
            request()->except(['sort', 'direction', 'page']),
            ['sort' => $col, 'direction' => $sortDir($col)]
        ));
        $trMonths = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart',    4 => 'Nisan',
            5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
            9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım',  12 => 'Aralık',
        ];
    @endphp

    <div class="space-y-6">

        {{-- Pie chart + hourly rate --}}
        @if (!empty($incomeTypeChart['labels']) || $averageHourlyRates->isNotEmpty())
            <section class="grid gap-6 md:grid-cols-2">
                @if (!empty($incomeTypeChart['labels']))
                    <div class="card-admin p-4 md:p-6">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Gelir Türü Dağılımı</h3>
                        <div class="mx-auto mt-4 h-64 max-w-sm">
                            <canvas id="incomeTypeChart"></canvas>
                        </div>
                    </div>
                @endif

                @if ($averageHourlyRates->isNotEmpty())
                    <div class="card-admin p-4 md:p-6">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Ortalama Saatlik Ücret</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sadece saat bilgisi girilen gelirlerden hesaplanır.</p>
                        <div class="mt-4 flex flex-wrap gap-6">
                            @foreach ($averageHourlyRates as $rate)
                                <div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format((float) $rate->avg_rate, 2) }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $rate->currency_code }} / saat</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        @endif

        {{-- Filters --}}
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <select name="year" class="form-input-admin">
                    <option value="">Tüm Yıllar</option>
                    @foreach ($availableYears as $y)
                        <option value="{{ $y }}" @selected((string) ($filters['year'] ?? '') === (string) $y)>{{ $y }}</option>
                    @endforeach
                </select>

                <select name="month" class="form-input-admin">
                    <option value="">Tüm Aylar</option>
                    @foreach ($trMonths as $num => $name)
                        <option value="{{ $num }}" @selected((string) ($filters['month'] ?? '') === (string) $num)>{{ $name }}</option>
                    @endforeach
                </select>

                <select name="sourceable" class="form-input-admin">
                    <option value="">Tüm Kaynaklar</option>
                    <optgroup label="{{ __('People') }}">
                        @foreach ($people as $person)
                            <option value="person:{{ $person->id }}" @selected(($filters['sourceable'] ?? '') === 'person:'.$person->id)>{{ $person->fullName() }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('Stakeholders') }}">
                        @foreach ($stakeholders as $stakeholder)
                            <option value="stakeholder:{{ $stakeholder->id }}" @selected(($filters['sourceable'] ?? '') === 'stakeholder:'.$stakeholder->id)>{{ $stakeholder->title ?: trim(($stakeholder->name ?? '').' '.($stakeholder->surname ?? '')) }}</option>
                        @endforeach
                    </optgroup>
                </select>

                <select name="income_type_id" class="form-input-admin">
                    <option value="">Tüm Türler</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected((string) ($filters['income_type_id'] ?? '') === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>

                <select name="currency_id" class="form-input-admin">
                    <option value="">Tüm Para Birimleri</option>
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency->id }}" @selected((string) ($filters['currency_id'] ?? '') === (string) $currency->id)>{{ $currency->code }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="admin-btn admin-btn-primary">Filtrele</button>
                    <a href="{{ route('admin.incomes.index') }}" class="admin-btn admin-btn-ghost">Sıfırla</a>
                </div>
            </form>
        </section>

        {{-- Table --}}
        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                <h2 class="text-sm font-semibold">Gelir Listesi</h2>
                <a href="{{ route('admin.incomes.create') }}" class="admin-btn admin-btn-primary">Yeni Gelir</a>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ $sortUrl('date') }}" class="inline-flex items-center gap-1 hover:text-gray-900 dark:hover:text-gray-100">
                                    Tarih {!! $sortIcon('date') !!}
                                </a>
                            </th>
                            <th>Kaynak</th>
                            <th>
                                <a href="{{ $sortUrl('type') }}" class="inline-flex items-center gap-1 hover:text-gray-900 dark:hover:text-gray-100">
                                    Tür {!! $sortIcon('type') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('amount') }}" class="inline-flex items-center gap-1 hover:text-gray-900 dark:hover:text-gray-100">
                                    Tutar {!! $sortIcon('amount') !!}
                                </a>
                            </th>
                            <th>Saat</th>
                            <th>Saatlik Ücret</th>
                            <th class="text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($incomes as $income)
                            <tr>
                                <td>{{ optional($income->date)->toDateString() }}</td>
                                <td>{{ $income->sourceableLabel() ?? (optional($income->source)->name ?: '-') }}</td>
                                <td>{{ optional($income->type)->name ?: '-' }}</td>
                                <td>{{ number_format((float) $income->amount, 2) }} {{ optional($income->currency)->code }}</td>
                                <td>{{ $income->hours ? number_format((float) $income->hours, 2) : '-' }}</td>
                                <td>{{ $income->hourlyRate() !== null ? number_format($income->hourlyRate(), 2).' '.optional($income->currency)->code : '-' }}</td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        {{-- Düzenle --}}
                                        <a href="{{ route('admin.incomes.edit', $income) }}"
                                           title="Düzenle"
                                           class="inline-flex items-center justify-center rounded-lg border border-gray-200 p-1.5 text-gray-600 transition hover:border-blue-300 hover:text-blue-700 dark:border-gray-700 dark:text-gray-300 dark:hover:border-blue-700 dark:hover:text-blue-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                            </svg>
                                        </a>

                                        {{-- Çoğalt --}}
                                        <form method="POST" action="{{ route('admin.incomes.duplicate', $income) }}">
                                            @csrf
                                            <button type="submit"
                                                    title="Çoğalt"
                                                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 p-1.5 text-gray-600 transition hover:border-gray-400 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-500 dark:hover:text-gray-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z"/>
                                                    <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z"/>
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Sil --}}
                                        <form method="POST" action="{{ route('admin.incomes.destroy', $income) }}" onsubmit="return confirm('Bu geliri silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="Sil"
                                                    class="inline-flex items-center justify-center rounded-lg border border-red-200 p-1.5 text-red-600 transition hover:border-red-400 hover:bg-red-50 hover:text-red-700 dark:border-red-900 dark:text-red-400 dark:hover:border-red-700 dark:hover:bg-red-950/30 dark:hover:text-red-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Gelir bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[var(--color-admin-border)] px-4 py-3 dark:border-[var(--color-admin-border-dark)]">
                {{ $incomes->links() }}
            </div>
        </section>
    </div>

    @if (!empty($incomeTypeChart['labels']))
        <script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
            document.addEventListener('DOMContentLoaded', function () {
                if (!window.Chart) return;
                const data = @json($incomeTypeChart);
                new window.Chart(document.getElementById('incomeTypeChart'), {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.values,
                            backgroundColor: [
                                '#22c55e', '#0ea5e9', '#f97316', '#8b5cf6',
                                '#ef4444', '#14b8a6', '#eab308', '#f43f5e',
                                '#6366f1', '#84cc16',
                            ],
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { position: 'right' },
                        },
                    },
                });
            });
        </script>
    @endif
@endsection
