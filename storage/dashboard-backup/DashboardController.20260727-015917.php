<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Consumption;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Person;
use App\Models\Post;
use App\Models\PurchaseItem;
use App\Models\Setting;
use App\Models\TimeRange;
use App\Models\TodoItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const DAILY_CALORIE_GOAL = 2500;

    private const DAILY_PROTEIN_GOAL = 140;

    private const DAILY_CARBS_GOAL = 350;

    private const DAILY_FAT_GOAL = 70;

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
            ->select(['id', 'name', 'surname', 'birthday', 'picture'])
            ->whereNotNull('birthday')
            ->whereNull('deathday')
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

        // Finansal settings — sadece ₺, USD dönüşümü kur üzerinden
        $fxUsdTry = data_get($rates, 'fx.rates.USD')
            ? 1 / (float) data_get($rates, 'fx.rates.USD')
            : null;

        $treasuryTry = (float) Setting::get('treasury_try', 0);
        $treasuryUsd = ($fxUsdTry && $fxUsdTry > 0) ? round($treasuryTry / $fxUsdTry, 2) : null;

        $dailyPassiveIncomeTry = (float) Setting::get('daily_passive_income_try', 0);
        $monthlyPassiveIncomeTry = $dailyPassiveIncomeTry * 30;
        $monthlyPassiveIncomeUsd = ($fxUsdTry && $fxUsdTry > 0)
            ? $monthlyPassiveIncomeTry / $fxUsdTry
            : 0;

        // Zenginlik seviyesi (USD eşik değerleri)
        $wealthThresholds = [250, 500, 750, 1500, 2500, 4000, 7500, 15000, 25000, 50000];
        $currentTierIndex = -1;
        foreach ($wealthThresholds as $i => $threshold) {
            if ($monthlyPassiveIncomeUsd >= $threshold) {
                $currentTierIndex = $i;
            }
        }
        if ($currentTierIndex === 9) {
            $wealthProgress = 100.0;
        } elseif ($currentTierIndex === -1) {
            $wealthProgress = $wealthThresholds[0] > 0
                ? min(100, round(($monthlyPassiveIncomeUsd / $wealthThresholds[0]) * 100, 2))
                : 0;
        } else {
            $diff = $wealthThresholds[$currentTierIndex + 1] - $wealthThresholds[$currentTierIndex];
            $above = $monthlyPassiveIncomeUsd - $wealthThresholds[$currentTierIndex];
            $wealthProgress = $diff > 0 ? min(100, round(($above / $diff) * 100, 2)) : 100.0;
        }

        // Bugünkü/gecikmiş yapılacaklar
        $dueTodos = TodoItem::query()
            ->where('is_completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now()->toDateString())
            ->orderBy('due_date')
            ->limit(10)
            ->get();
        $dueTodosTotal = TodoItem::where('is_completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now()->toDateString())
            ->count();

        // Bucketlist istatistikleri
        $bucketlistTotal = PurchaseItem::where('is_bucketlist', true)->count()
            + TodoItem::where('is_bucketlist', true)->count();
        $bucketlistCompleted = PurchaseItem::where('is_bucketlist', true)->where('is_completed', true)->count()
            + TodoItem::where('is_bucketlist', true)->where('is_completed', true)->count();

        $clockRing = $this->buildClockRing();

        $todayConsumptions = Consumption::query()
            ->whereDate('consumed_on', now()->toDateString())
            ->with('food')
            ->get();
        $dailyCalories = $todayConsumptions->sum(fn (Consumption $c) => $c->calories());
        $dailyCarbs = $todayConsumptions->sum(fn (Consumption $c) => $c->carbs());
        $dailyFat = $todayConsumptions->sum(fn (Consumption $c) => $c->fat());
        $dailyProtein = $todayConsumptions->sum(fn (Consumption $c) => $c->protein());

        $calorieGoalRatio = $dailyCalories / self::DAILY_CALORIE_GOAL;
        $calorieGoalPercent = min(100, round($calorieGoalRatio * 100));
        $calorieGoalStatus = match (true) {
            $calorieGoalRatio > 1.25 => 'danger',
            $calorieGoalRatio < 0.75 => 'warning',
            default => 'success',
        };

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
            'treasuryTry' => $treasuryTry,
            'treasuryUsd' => $treasuryUsd,
            'dailyPassiveIncomeTry' => $dailyPassiveIncomeTry,
            'monthlyPassiveIncomeTry' => $monthlyPassiveIncomeTry,
            'monthlyPassiveIncomeUsd' => $monthlyPassiveIncomeUsd,
            'currentTierIndex' => $currentTierIndex,
            'wealthThresholds' => $wealthThresholds,
            'wealthProgress' => $wealthProgress,
            'dueTodos' => $dueTodos,
            'dueTodosTotal' => $dueTodosTotal,
            'bucketlistTotal' => $bucketlistTotal,
            'bucketlistCompleted' => $bucketlistCompleted,
            'clockRing' => $clockRing,
            'dailyCalories' => $dailyCalories,
            'dailyCarbs' => $dailyCarbs,
            'dailyFat' => $dailyFat,
            'dailyProtein' => $dailyProtein,
            'calorieGoal' => self::DAILY_CALORIE_GOAL,
            'calorieGoalPercent' => $calorieGoalPercent,
            'calorieGoalStatus' => $calorieGoalStatus,
            'proteinGoal' => self::DAILY_PROTEIN_GOAL,
            'carbsGoal' => self::DAILY_CARBS_GOAL,
            'fatGoal' => self::DAILY_FAT_GOAL,
        ]);
    }

    /**
     * Builds a 24h conic-gradient ring (per current day-of-week's TimeRange rows)
     * plus the currently active range's label/color for the center dial.
     */
    private function buildClockRing(): array
    {
        // Soft near-white blue-gray for hours with no defined range (was flat gray).
        $defaultColor = '#E2E8F0';
        $minutesInDay = 1440;

        $ranges = TimeRange::query()
            ->where('day_of_week', now()->dayOfWeek)
            ->orderBy('starts_at')
            ->get();

        $toMinutes = function (string $time): int {
            [$hours, $minutes] = array_map('intval', explode(':', $time));

            return $hours * 60 + $minutes;
        };

        $segments = [];
        foreach ($ranges as $range) {
            $start = $toMinutes((string) $range->starts_at);
            $end = $toMinutes((string) $range->ends_at);

            if ($end <= $start) {
                $segments[] = ['start' => $start, 'end' => $minutesInDay, 'color' => $range->color, 'label' => $range->label];
                $segments[] = ['start' => 0, 'end' => $end, 'color' => $range->color, 'label' => $range->label];

                continue;
            }

            $segments[] = ['start' => $start, 'end' => $end, 'color' => $range->color, 'label' => $range->label];
        }

        usort($segments, fn (array $a, array $b) => $a['start'] <=> $b['start']);

        $pct = fn (int $minutes): string => rtrim(rtrim(number_format($minutes / $minutesInDay * 100, 4), '0'), '.');

        $stops = [];
        $cursor = 0;
        foreach ($segments as $segment) {
            if ($segment['start'] > $cursor) {
                $stops[] = sprintf('%s %s%% %s%%', $defaultColor, $pct($cursor), $pct($segment['start']));
            }
            $stops[] = sprintf('%s %s%% %s%%', $segment['color'], $pct($segment['start']), $pct($segment['end']));
            $cursor = max($cursor, $segment['end']);
        }
        if ($cursor < $minutesInDay) {
            $stops[] = sprintf('%s %s%% 100%%', $defaultColor, $pct($cursor));
        }
        if ($stops === []) {
            $stops[] = sprintf('%s 0%% 100%%', $defaultColor);
        }

        $nowMinutes = now()->hour * 60 + now()->minute;
        $current = collect($segments)->first(fn (array $segment) => $nowMinutes >= $segment['start'] && $nowMinutes < $segment['end']);
        $currentColor = $current['color'] ?? $defaultColor;

        return [
            'gradient' => implode(', ', $stops),
            'currentLabel' => $current['label'] ?? __('Tanımsız'),
            'currentColor' => $currentColor,
            'currentTextColor' => $this->contrastingTextColor($currentColor),
            'currentTime' => now()->format('H:i'),
        ];
    }

    /**
     * Picks near-black or white text depending on the background's perceived
     * luminance, since range colors (and the light default) are user-chosen
     * and can be light enough that white text would be unreadable.
     */
    private function contrastingTextColor(string $hexColor): string
    {
        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.6 ? '#1F2937' : '#FFFFFF';
    }
}
