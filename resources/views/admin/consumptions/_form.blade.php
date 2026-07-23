@php($isEdit = isset($consumption))
@php($foodUnitMap = $foods->mapWithKeys(fn ($food) => [$food->id => $food->unit_type])->all())
@php($initialFoodId = (string) old('food_id', $consumption->food_id ?? ($foods->first()->id ?? '')))

<div
    x-data="{ foodUnits: {{ Js::from($foodUnitMap) }}, foodId: '{{ $initialFoodId }}' }"
    class="space-y-4"
>
    <div class="grid gap-4 md:grid-cols-2">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block">Tarih</span>
            <input type="date" name="consumed_on" class="form-input-admin" value="{{ old('consumed_on', isset($consumption) ? optional($consumption->consumed_on)->toDateString() : now()->toDateString()) }}" required>
            @error('consumed_on')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block">Besin</span>
            <select name="food_id" x-model="foodId" class="form-input-admin" required>
                @foreach ($foods as $food)
                    <option value="{{ $food->id }}" @selected((string) $food->id === $initialFoodId)>{{ $food->name }}</option>
                @endforeach
            </select>
            @error('food_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            <span class="mb-1 block" x-text="foodUnits[foodId] === 'piece' ? 'Miktar (adet)' : 'Miktar (gram)'"></span>
            <input type="number" step="0.01" min="0.01" name="quantity" class="form-input-admin" value="{{ old('quantity', $consumption->quantity ?? '') }}" required>
            @error('quantity')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>
    </div>

    <div class="flex items-center gap-3">
        <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900" type="submit">
            {{ $isEdit ? 'Güncelle' : 'Oluştur' }}
        </button>

        <a href="{{ route('admin.consumptions.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700">
            İptal
        </a>
    </div>
</div>
