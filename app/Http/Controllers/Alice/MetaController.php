<?php

namespace App\Http\Controllers\Alice;

use App\Models\BloodType;
use App\Models\Currency;
use App\Models\ExpenseType;
use App\Models\EyeColor;
use App\Models\Gender;
use App\Models\HairColor;
use App\Models\IncomeSource;
use App\Models\IncomeType;
use App\Models\InteractionType;
use App\Models\PostCategory;
use App\Models\PostLanguage;
use Illuminate\Http\JsonResponse;

class MetaController extends AliceController
{
    public function currencies(): JsonResponse
    {
        return response()->json(['data' => Currency::orderBy('code')->get(['id', 'code', 'name', 'symbol'])]);
    }

    public function expenseTypes(): JsonResponse
    {
        return response()->json(['data' => ExpenseType::orderBy('name')->get(['id', 'name', 'government_acceptance_percentage'])]);
    }

    public function incomeSources(): JsonResponse
    {
        return response()->json(['data' => IncomeSource::orderBy('name')->get(['id', 'name'])]);
    }

    public function incomeTypes(): JsonResponse
    {
        return response()->json(['data' => IncomeType::orderBy('name')->get(['id', 'name'])]);
    }

    public function interactionTypes(): JsonResponse
    {
        return response()->json(['data' => InteractionType::orderBy('name')->get(['id', 'name'])]);
    }

    public function genders(): JsonResponse
    {
        return response()->json(['data' => Gender::orderBy('name')->get(['id', 'name', 'slug'])]);
    }

    public function eyeColors(): JsonResponse
    {
        return response()->json(['data' => EyeColor::orderBy('name')->get(['id', 'name', 'slug'])]);
    }

    public function bloodTypes(): JsonResponse
    {
        return response()->json(['data' => BloodType::orderBy('name')->get(['id', 'name'])]);
    }

    public function hairColors(): JsonResponse
    {
        return response()->json(['data' => HairColor::orderBy('name')->get(['id', 'name', 'slug'])]);
    }

    public function postCategories(): JsonResponse
    {
        return response()->json(['data' => PostCategory::orderBy('name')->get(['id', 'name', 'slug'])]);
    }

    public function postLanguages(): JsonResponse
    {
        return response()->json(['data' => PostLanguage::orderBy('name')->get(['id', 'name', 'code'])]);
    }
}
