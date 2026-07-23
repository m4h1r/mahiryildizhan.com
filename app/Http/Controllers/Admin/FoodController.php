<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFoodRequest;
use App\Models\Food;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FoodController extends Controller
{
    public function index(Request $request): View
    {
        $query = Food::query()->orderBy('name');

        if ($search = trim((string) $request->string('search'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        return view('admin.foods.index', [
            'title' => 'Besinler',
            'heading' => 'Besinler',
            'foods' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): View
    {
        return view('admin.foods.create', [
            'title' => 'Yeni Besin',
            'heading' => 'Yeni Besin',
        ]);
    }

    public function store(StoreFoodRequest $request): RedirectResponse
    {
        Food::query()->create($request->validated());

        return to_route('admin.foods.index')->with('success', 'Besin oluşturuldu.');
    }

    public function edit(Food $food): View
    {
        return view('admin.foods.edit', [
            'title' => 'Besin Düzenle',
            'heading' => 'Besin Düzenle',
            'food' => $food,
        ]);
    }

    public function update(StoreFoodRequest $request, Food $food): RedirectResponse
    {
        $food->update($request->validated());

        return to_route('admin.foods.index')->with('success', 'Besin güncellendi.');
    }

    public function destroy(Food $food): RedirectResponse
    {
        $food->delete();

        return to_route('admin.foods.index')->with('success', 'Besin silindi.');
    }
}
