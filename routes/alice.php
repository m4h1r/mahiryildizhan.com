<?php

use App\Http\Controllers\Alice\AdageController;
use App\Http\Controllers\Alice\ConsumptionController;
use App\Http\Controllers\Alice\ExpenseController;
use App\Http\Controllers\Alice\FoodController;
use App\Http\Controllers\Alice\IncomeController;
use App\Http\Controllers\Alice\InteractionController;
use App\Http\Controllers\Alice\MetaController;
use App\Http\Controllers\Alice\NodeController;
use App\Http\Controllers\Alice\PersonAssessmentController;
use App\Http\Controllers\Alice\PersonChannelController;
use App\Http\Controllers\Alice\PersonController;
use App\Http\Controllers\Alice\PersonFaceController;
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
            'version' => '1.2.0',
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
            Route::get('/vitamins', [MetaController::class, 'vitamins']);
            Route::get('/person-card-schema', [MetaController::class, 'personCardSchema']);
        });

        // Settings
        Route::get('/settings', [SettingController::class, 'index']);
        Route::patch('/settings/{key}', [SettingController::class, 'update']);

        // Financial
        Route::apiResource('expenses', ExpenseController::class)->except(['create', 'edit']);
        Route::apiResource('incomes', IncomeController::class)->except(['create', 'edit']);

        // People
        Route::apiResource('people', PersonController::class)->except(['create', 'edit']);

        // Person card (CRM): channels, assessments (personality inventory), faces
        Route::get('people/{person}/card', [PersonController::class, 'card'])->whereNumber('person');
        Route::prefix('people/{person}')->whereNumber('person')->group(function () {
            Route::get('channels', [PersonChannelController::class, 'index']);
            Route::post('channels', [PersonChannelController::class, 'store']);
            Route::match(['put', 'patch'], 'channels/{channel}', [PersonChannelController::class, 'update'])->whereNumber('channel');
            Route::delete('channels/{channel}', [PersonChannelController::class, 'destroy'])->whereNumber('channel');

            Route::get('assessments', [PersonAssessmentController::class, 'index']);
            Route::post('assessments', [PersonAssessmentController::class, 'store']);
            Route::get('assessments/{assessment}', [PersonAssessmentController::class, 'show'])->whereNumber('assessment');
            Route::match(['put', 'patch'], 'assessments/{assessment}', [PersonAssessmentController::class, 'update'])->whereNumber('assessment');
            Route::delete('assessments/{assessment}', [PersonAssessmentController::class, 'destroy'])->whereNumber('assessment');

            Route::get('faces', [PersonFaceController::class, 'index']);
            Route::post('faces', [PersonFaceController::class, 'store']);
            Route::get('faces/{face}', [PersonFaceController::class, 'show'])->whereNumber('face');
            Route::get('faces/{face}/image', [PersonFaceController::class, 'image'])->whereNumber('face');
            Route::delete('faces/{face}', [PersonFaceController::class, 'destroy'])->whereNumber('face');
        });
        Route::apiResource('stakeholders', StakeholderController::class)->except(['create', 'edit']);
        Route::apiResource('interactions', InteractionController::class)->except(['create', 'edit']);

        // Content
        Route::apiResource('posts', PostController::class)->except(['create', 'edit']);

        // Personal management
        Route::apiResource('todo-items', TodoItemController::class)->except(['create', 'edit']);
        Route::apiResource('purchase-items', PurchaseItemController::class)->except(['create', 'edit']);

        // Nutrition tracking
        Route::apiResource('foods', FoodController::class)->except(['create', 'edit']);
        Route::apiResource('consumptions', ConsumptionController::class)->except(['create', 'edit']);

        // Knowledge & Timeline
        Route::apiResource('nodes', NodeController::class)->except(['create', 'edit']);
        Route::apiResource('timeline-events', TimelineEventController::class)->except(['create', 'edit']);
        Route::apiResource('adages', AdageController::class)->except(['create', 'edit']);

        // Subscribers
        Route::apiResource('subscribers', SubscriberController::class)->except(['create', 'edit']);
    });
