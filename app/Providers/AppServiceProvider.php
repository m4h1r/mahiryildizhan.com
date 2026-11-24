<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Person;
use App\Models\Post;
use App\Models\PurchaseItem;
use App\Models\Setting;
use App\Models\TodoItem;
use App\Observers\ActivityLogObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
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
        foreach ([Expense::class, Income::class, Post::class, Person::class, Comment::class, TodoItem::class, PurchaseItem::class] as $observedModel) {
            $observedModel::observe(ActivityLogObserver::class);
        }

        Password::defaults(fn () => Password::min(12)->uncompromised());

        // Alice (Pi) bridge: cap at config('alice.rate_limit') req/min per token/IP;
        // the throttle middleware answers 429 with Retry-After automatically.
        RateLimiter::for('alice', function (Request $request) {
            $key = optional($request->user())->getAuthIdentifier() ?: $request->ip();

            return Limit::perMinute((int) config('alice.rate_limit', 120))->by('alice:'.$key);
        });

        // Pulse dashboard (W6) — admin only, in every environment.
        Gate::define('viewPulse', fn ($user = null) => (bool) optional($user)->is_admin);

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
