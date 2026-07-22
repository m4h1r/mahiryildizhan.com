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
