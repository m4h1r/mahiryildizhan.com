@php($isEdit = isset($food))
@php($vitaminValues = old('vitamins', $food->vitamins ?? []))

<div x-data="{ unitType: '{{ old('unit_type', $food->unit_type ?? 'gram') }}' }" class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2">
        <label class="md:col-span-2 text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block">Ad</span>
            <input type="text" name="name" class="form-input-admin" value="{{ old('name', $food->name ?? '') }}" required>
            @error('name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block">Kalori (100g başına, kcal)</span>
            <input type="number" step="1" min="0" name="calories_per_100g" class="form-input-admin" value="{{ old('calories_per_100g', $food->calories_per_100g ?? '') }}" required>
            @error('calories_per_100g')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block">Karbonhidrat (100g başına, g / %)</span>
            <input type="number" step="0.1" min="0" max="100" name="carbs_per_100g" class="form-input-admin" value="{{ old('carbs_per_100g', $food->carbs_per_100g ?? 0) }}" required>
            @error('carbs_per_100g')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block">Şeker (100g başına, g / %)</span>
            <input type="number" step="0.1" min="0" max="100" name="sugar_per_100g" class="form-input-admin" value="{{ old('sugar_per_100g', $food->sugar_per_100g ?? 0) }}" required>
            @error('sugar_per_100g')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block">Yağ (100g başına, g / %)</span>
            <input type="number" step="0.1" min="0" max="100" name="fat_per_100g" class="form-input-admin" value="{{ old('fat_per_100g', $food->fat_per_100g ?? 0) }}" required>
            @error('fat_per_100g')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block">Ölçü Birimi</span>
            <select name="unit_type" x-model="unitType" class="form-input-admin">
                <option value="gram">Gram</option>
                <option value="piece">Adet</option>
            </select>
        </label>

        <label class="text-sm font-medium text-gray-700 dark:text-gray-200" x-show="unitType === 'piece'" x-cloak>
            <span class="mb-1 block">1 Adet Kaç Gram?</span>
            <input type="number" step="any" min="0.01" name="grams_per_unit" class="form-input-admin" value="{{ old('grams_per_unit', $food->grams_per_unit ?? '') }}">
            @error('grams_per_unit')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>
    </div>

    <div>
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vitamin ve Mineraller (100g başına, isteğe bağlı)</h3>
        <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
            @foreach (\App\Models\Food::VITAMINS as $key => $label)
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    <span class="mb-1 block text-xs">{{ $label }}</span>
                    <input type="number" step="0.01" min="0" name="vitamins[{{ $key }}]" class="form-input-admin" value="{{ $vitaminValues[$key] ?? '' }}">
                </label>
            @endforeach
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
            {{ $isEdit ? 'Güncelle' : 'Oluştur' }}
        </button>

        <a href="{{ route('admin.foods.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">
            İptal
        </a>
    </div>
</div>
