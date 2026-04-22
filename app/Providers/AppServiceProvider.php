<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Setting;
use App\Observers\ExpenseObserver;
use App\Observers\IncomeObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Expense::observe(ExpenseObserver::class);
        Income::observe(IncomeObserver::class);

        try {
            if (Schema::hasTable('settings')) {
                $recaptchaSiteKey = config('services.recaptcha.site_key') ?: Setting::get('recaptcha_site_key');
                $recaptchaSecret = config('services.recaptcha.secret') ?: Setting::get('recaptcha_secret_key');

                if ($recaptchaSiteKey) {
                    config(['services.recaptcha.site_key' => $recaptchaSiteKey]);
                }

                if ($recaptchaSecret) {
                    config(['services.recaptcha.secret' => $recaptchaSecret]);
                }
            }
        } catch (Throwable) {
            // Allow app/CLI boot to continue when the database is temporarily unavailable.
        }
    }
}
