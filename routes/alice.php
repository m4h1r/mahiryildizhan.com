<?php

use App\Http\Controllers\Alice\AdageController;
use App\Http\Controllers\Alice\ExpenseController;
use App\Http\Controllers\Alice\IncomeController;
use App\Http\Controllers\Alice\InteractionController;
use App\Http\Controllers\Alice\MetaController;
use App\Http\Controllers\Alice\NodeController;
use App\Http\Controllers\Alice\PersonController;
use App\Http\Controllers\Alice\PostController;
use App\Http\Controllers\Alice\PurchaseItemController;
use App\Http\Controllers\Alice\SettingController;
use App\Http\Controllers\Alice\StakeholderController;
use App\Http\Controllers\Alice\SubscriberController;
use App\Http\Controllers\Alice\TimelineEventController;
use App\Http\Controllers\Alice\TodoItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/alice')
    ->middleware(['alice.auth', 'alice.idempotency', 'alice.dryrun', 'alice.audit'])
    ->group(function () {

        // Health check (auth only, no audit)
        Route::get('/health', fn () => response()->json([
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]));

        // Meta / Lookup endpoints (read-only, no write middleware needed)
        Route::prefix('meta')->group(function () {
            Route::get('/currencies', [MetaController::class, 'currencies']);
            Route::get('/expense-types', [MetaController::class, 'expenseTypes']);
            Route::get('/income-sources', [MetaController::class, 'incomeSources']);
            Route::get('/income-types', [MetaController::class, 'incomeTypes']);
            Route::get('/interaction-types', [MetaController::class, 'interactionTypes']);
            Route::get('/genders', [MetaController::class, 'genders']);
            Route::get('/eye-colors', [MetaController::class, 'eyeColors']);
            Route::get('/blood-types', [MetaController::class, 'bloodTypes']);
            Route::get('/hair-colors', [MetaController::class, 'hairColors']);
            Route::get('/post-categories', [MetaController::class, 'postCategories']);
            Route::get('/post-languages', [MetaController::class, 'postLanguages']);
        });

        // Settings
        Route::get('/settings', [SettingController::class, 'index']);
        Route::patch('/settings/{key}', [SettingController::class, 'update']);

        // Financial
        Route::apiResource('expenses', ExpenseController::class)->except(['create', 'edit']);
        Route::apiResource('incomes', IncomeController::class)->except(['create', 'edit']);

        // People
        Route::apiResource('people', PersonController::class)->except(['create', 'edit']);
        Route::apiResource('stakeholders', StakeholderController::class)->except(['create', 'edit']);
        Route::apiResource('interactions', InteractionController::class)->except(['create', 'edit']);

        // Content
        Route::apiResource('posts', PostController::class)->except(['create', 'edit']);

        // Personal management
        Route::apiResource('todo-items', TodoItemController::class)->except(['create', 'edit']);
        Route::apiResource('purchase-items', PurchaseItemController::class)->except(['create', 'edit']);

        // Knowledge & Timeline
        Route::apiResource('nodes', NodeController::class)->except(['create', 'edit']);
        Route::apiResource('timeline-events', TimelineEventController::class)->except(['create', 'edit']);
        Route::apiResource('adages', AdageController::class)->except(['create', 'edit']);

        // Subscribers
        Route::apiResource('subscribers', SubscriberController::class)->except(['create', 'edit']);
    });
