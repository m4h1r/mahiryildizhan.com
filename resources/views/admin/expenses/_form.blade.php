@php($isEdit = isset($expense))
@php($initialStakeholderId = (string) old('stakeholder_id', $expense->stakeholder_id ?? ''))
@php($selectedStakeholder = $stakeholders->firstWhere('id', (int) $initialStakeholderId))
@php($stakeholderOptions = $stakeholders->map(fn ($stakeholder) => [
    'id' => $stakeholder->id,
    'title' => $stakeholder->title ?: trim(($stakeholder->name ?? '').' '.($stakeholder->surname ?? '')),
    'vkn_tckn' => $stakeholder->vkn_tckn,
])->values())

<div
    x-data="expenseStakeholderLookup({
        lookupUrl: '{{ route('api.stakeholders.lookup') }}',
        quickCreateUrl: '{{ route('api.stakeholders.quick') }}',
        stakeholders: {{ Js::from($stakeholderOptions) }},
        initialSelectedId: '{{ $initialStakeholderId }}',
        initialVkn: '{{ old('stakeholder_vkn_lookup', $selectedStakeholder?->vkn_tckn ?? '') }}'
    })"
    x-init="init()"
    class="space-y-4"
>
<div class="grid gap-4 md:grid-cols-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Tarih</span>
        <input type="date" name="date" class="form-input-admin" value="{{ old('date', isset($expense) ? optional($expense->date)->toDateString() : now()->toDateString()) }}" required>
        @error('date')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">VKN / TCKN Ara</span>
        <div class="relative">
            <input
                type="text"
                name="stakeholder_vkn_lookup"
                x-model="vkn"
                @input="onVknInput()"
                class="form-input-admin pr-10"
                placeholder="Paydaş bulmak için vergi kimliği girin"
                autocomplete="off"
            >
            <span x-show="loadingLookup" x-cloak class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-gray-500">Kontrol ediliyor...</span>
        </div>
        <span class="mt-1 block text-xs text-gray-500" x-show="matchedTitle" x-text="matchedTitle"></span>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Paydaş</span>
        <select name="stakeholder_id" x-model="selectedId" @change="syncFromSelected()" class="form-input-admin">
            <option value="">Yok</option>
            <template x-for="stakeholder in stakeholders" :key="stakeholder.id">
                <option :value="String(stakeholder.id)" x-text="stakeholder.title"></option>
            </template>
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Gider Türü</span>
        <select name="expense_type_id" class="form-input-admin">
            <option value="">Yok</option>
            @foreach ($expenseTypes as $expenseType)
                <option value="{{ $expenseType->id }}" @selected((string) old('expense_type_id', $expense->expense_type_id ?? $defaultExpenseTypeId ?? '') === (string) $expenseType->id)>{{ $expenseType->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Para Birimi</span>
        <select name="currency_id" class="form-input-admin" required>
            @foreach ($currencies as $currency)
                <option value="{{ $currency->id }}" @selected((string) old('currency_id', $expense->currency_id ?? $defaultCurrencyId ?? '') === (string) $currency->id)>{{ $currency->code }} - {{ $currency->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Fiyat</span>
        <input type="number" step="0.01" min="0" name="price" class="form-input-admin" value="{{ old('price', $expense->price ?? 0) }}" required>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Adet</span>
        <input type="number" step="0.001" min="0" name="quantity" class="form-input-admin" value="{{ old('quantity', $expense->quantity ?? 1) }}" required>
    </label>

    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Vergi</span>
        <input type="number" step="0.01" min="0" name="tax" class="form-input-admin" value="{{ old('tax', $expense->tax ?? 0) }}">
    </label>

    <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
        <span class="mb-1 block">Açıklama</span>
        <textarea name="description" class="form-input-admin min-h-28">{{ old('description', $expense->description ?? '') }}</textarea>
    </label>

    <label class="flex items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-200">
        <input type="checkbox" name="company_expense" value="1" @checked(old('company_expense', $expense->company_expense ?? false))>
        Şirket gideri
    </label>

    <label class="flex items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-200">
        <input type="checkbox" name="paid_by_others" value="1" @checked(old('paid_by_others', $expense->paid_by_others ?? false))>
        Başkası ödedi
    </label>
</div>

<div
    x-cloak
    x-show="showQuickCreateModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 px-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="quick-stakeholder-title"
>
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="quick-stakeholder-title" class="text-lg font-semibold">Paydaş oluştur</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Bu VKN/TCKN için paydaş bulunamadı. Yeni bir paydaş oluşturun ve gidere bağlayın.</p>
            </div>
            <button type="button" @click="closeQuickCreateModal()" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700" aria-label="Close quick create modal">Kapat</button>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200 md:col-span-2">
                <span class="mb-1 block">VKN / TCKN</span>
                <input type="text" x-model="vkn" class="form-input-admin" readonly tabindex="-1">
            </label>

            <label class="text-sm font-medium text-gray-700 dark:text-gray-200 md:col-span-2">
                <span class="mb-1 block">Ünvan / Şirket</span>
                <input type="text" x-model="quickTitle" class="form-input-admin" placeholder="Şirket veya görünen isim">
            </label>

            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                <span class="mb-1 block">Ad</span>
                <input type="text" x-model="quickName" class="form-input-admin" placeholder="İsteğe bağlı">
            </label>

            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                <span class="mb-1 block">Soyad</span>
                <input type="text" x-model="quickSurname" class="form-input-admin" placeholder="İsteğe bağlı">
            </label>
        </div>

        <p x-show="quickError" x-text="quickError" class="mt-3 text-sm text-red-600"></p>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" @click="closeQuickCreateModal()" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">İptal</button>
            <button type="button" @click="createQuickStakeholder()" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">Oluştur ve bağla</button>
        </div>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
        {{ $isEdit ? 'Güncelle' : 'Oluştur' }}
    </button>

    <a href="{{ route('admin.expenses.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">
        İptal
    </a>
</div>
</div>
