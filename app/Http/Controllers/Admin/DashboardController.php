<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Person;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $latitude = (float) (config('weather.latitude') ?: Setting::get('weather_latitude', '40.7654'));
        $longitude = (float) (config('weather.longitude') ?: Setting::get('weather_longitude', '29.9404'));
        $weatherCityName = (string) (config('weather.city_name') ?: Setting::get('weather_city_name', 'Kocaeli'));

        $weather = Cache::remember('weather_kocaeli', 900, function () use ($latitude, $longitude): ?array {
            $response = Http::timeout(8)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'daily' => 'weathercode,temperature_2m_max,temperature_2m_min',
                'forecast_days' => 5,
                'timezone' => 'Europe/Istanbul',
            ]);

            return $response->ok() ? $response->json() : null;
        });

        $rates = Cache::remember('dashboard_exchange_rates', 900, function (): array {
            $cryptoResponse = Http::timeout(8)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'bitcoin,ethereum',
                'vs_currencies' => 'usd',
            ]);

            $fxResponse = Http::timeout(8)->get('https://api.exchangerate-api.com/v4/latest/TRY');

            return [
                'crypto' => $cryptoResponse->ok() ? $cryptoResponse->json() : null,
                'fx' => $fxResponse->ok() ? $fxResponse->json() : null,
            ];
        });

        $publishedPosts = Post::query()->where('status', 'published')->count();
        $pendingComments = Comment::query()->where('is_approved', false)->count();

        $tryCurrencyId = Currency::query()->where('code', 'TRY')->value('id');

        $expenseBaseQuery = Expense::query()
            ->where('paid_by_others', false)
            ->whereYear('date', now()->year);

        $incomeBaseQuery = Income::query()
            ->whereYear('date', now()->year);

        if ($tryCurrencyId) {
            $expenseBaseQuery->where('currency_id', $tryCurrencyId);
            $incomeBaseQuery->where('currency_id', $tryCurrencyId);
        }

        $monthlyExpense = (float) (clone $expenseBaseQuery)
            ->whereMonth('date', now()->month)
            ->sum('total');
        $monthlyIncome = (float) (clone $incomeBaseQuery)
            ->whereMonth('date', now()->month)
            ->sum('amount');

        $annualExpense = (float) (clone $expenseBaseQuery)->sum('total');

        $annualIncome = (float) (clone $incomeBaseQuery)->sum('amount');

        $peopleCount = Person::query()->count();
        $approvedComments = Comment::query()->where('is_approved', true)->count();

        $recentPosts = Post::query()
            ->select(['id', 'title', 'status', 'published_at', 'updated_at'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $recentComments = Comment::query()
            ->with('post:id,title')
            ->select(['id', 'post_id', 'guest_name', 'body', 'is_approved', 'created_at'])
            ->latest('created_at')
            ->limit(6)
            ->get();

        $today = now()->startOfDay();
        $windowDays = 7;
        $upcomingBirthdays = Person::query()
            ->select(['id', 'name', 'surname', 'birthday', 'picture', 'deathday'])
            ->whereNotNull('birthday')
            ->get()
            ->map(function (Person $person) use ($today): Person {
                $birthday = Carbon::parse((string) $person->birthday);
                $candidates = [
                    Carbon::create($today->year - 1, $birthday->month, $birthday->day)->startOfDay(),
                    Carbon::create($today->year, $birthday->month, $birthday->day)->startOfDay(),
                    Carbon::create($today->year + 1, $birthday->month, $birthday->day)->startOfDay(),
                ];

                $closestBirthday = collect($candidates)
                    ->sortBy(fn (Carbon $candidate) => abs($today->diffInDays($candidate, false)))
                    ->first();

                $daysFromToday = $today->diffInDays($closestBirthday, false);

                $person->setAttribute('closest_birthday', $closestBirthday);
                $person->setAttribute('days_from_today', $daysFromToday);

                return $person;
            })
            ->filter(fn (Person $person) => abs((int) $person->days_from_today) <= $windowDays)
            ->sortBy(fn (Person $person) => [(int) $person->days_from_today, $person->name])
            ->take(12)
            ->values();

        return view('admin.dashboard', [
            'publishedPosts' => $publishedPosts,
            'pendingComments' => $pendingComments,
            'approvedComments' => $approvedComments,
            'peopleCount' => $peopleCount,
            'monthlyNet' => $monthlyIncome - $monthlyExpense,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'annualIncome' => $annualIncome,
            'annualExpense' => $annualExpense,
            'recentPosts' => $recentPosts,
            'recentComments' => $recentComments,
            'upcomingBirthdays' => $upcomingBirthdays,
            'weather' => $weather,
            'weatherCityName' => $weatherCityName,
            'rates' => $rates,
        ]);
    }
}