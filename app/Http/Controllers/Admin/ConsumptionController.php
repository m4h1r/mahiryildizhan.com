<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConsumptionRequest;
use App\Models\Consumption;
use App\Models\Food;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsumptionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Consumption::query()
            ->with('food')
            ->latest('consumed_on')
            ->latest('id');

        if ($dateFrom = $request->date('date_from')) {
            $query->whereDate('consumed_on', '>=', $dateFrom);
        }

        if ($dateTo = $request->date('date_to')) {
            $query->whereDate('consumed_on', '<=', $dateTo);
        }

        if ($foodId = $request->integer('food_id')) {
            $query->where('food_id', $foodId);
        }

        return view('admin.consumptions.index', [
            'title' => 'Tüketimler',
            'heading' => 'Tüketimler',
            'consumptions' => $query->paginate(20)->withQueryString(),
            'foods' => Food::query()->orderBy('name')->get(),
            'filters' => $request->only(['date_from', 'date_to', 'food_id']),
        ]);
    }

    public function create(): View
    {
        return view('admin.consumptions.create', [
            'title' => 'Yeni Tüketim',
            'heading' => 'Yeni Tüketim',
            'foods' => Food::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreConsumptionRequest $request): RedirectResponse
    {
        Consumption::query()->create($request->validated());

        return to_route('admin.consumptions.index')->with('success', 'Tüketim kaydedildi.');
    }

    public function edit(Consumption $consumption): View
    {
        return view('admin.consumptions.edit', [
            'title' => 'Tüketim Düzenle',
            'heading' => 'Tüketim Düzenle',
            'consumption' => $consumption,
            'foods' => Food::query()->orderBy('name')->get(),
        ]);
    }

    public function update(StoreConsumptionRequest $request, Consumption $consumption): RedirectResponse
    {
        $consumption->update($request->validated());

        return to_route('admin.consumptions.index')->with('success', 'Tüketim güncellendi.');
    }

    public function destroy(Consumption $consumption): RedirectResponse
    {
        $consumption->delete();

        return to_route('admin.consumptions.index')->with('success', 'Tüketim silindi.');
    }
}
