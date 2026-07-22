<?php

use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdageController;
use App\Http\Controllers\Admin\BucketlistController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\CsvImportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DictionaryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\InteractionController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NodeConnectionController;
use App\Http\Controllers\Admin\NodeController;
use App\Http\Controllers\Admin\PersonController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PurchaseItemController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StakeholderController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\TimelineController;
use App\Http\Controllers\Admin\TodoItemController;
use App\Services\CsvImportService;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('admin.activity-log.index');

// Purchase Items
Route::patch('/purchase-items/{purchaseItem}/toggle-complete', [PurchaseItemController::class, 'toggleComplete'])
    ->name('admin.purchase-items.toggle-complete');
Route::resource('/purchase-items', PurchaseItemController::class)
    ->except(['show'])->names('admin.purchase-items');

// Todo Items
Route::patch('/todo-items/{todoItem}/toggle-complete', [TodoItemController::class, 'toggleComplete'])
    ->name('admin.todo-items.toggle-complete');
Route::resource('/todo-items', TodoItemController::class)
    ->except(['show'])->names('admin.todo-items');

// Bucketlist
Route::get('/bucketlist', [BucketlistController::class, 'index'])->name('admin.bucketlist');

Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
Route::get('/about', [AboutController::class, 'index'])->name('admin.about');
Route::post('/about', [AboutController::class, 'update'])->name('admin.about.update');
Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings');
Route::post('/settings', [SettingController::class, 'update'])->name('admin.settings.update');

Route::get('/import', [CsvImportController::class, 'index'])->name('admin.import.index');
Route::post('/import', [CsvImportController::class, 'import'])->name('admin.import.run');
Route::post('/import/truncate-all', [CsvImportController::class, 'truncateAll'])->name('admin.import.truncate');
Route::post('/import/{table}', [CsvImportController::class, 'import'])
    ->whereIn('table', CsvImportService::supportedTables())
    ->name('admin.import.table');

Route::post('/stakeholders/{stakeholder}/duplicate', [StakeholderController::class, 'duplicate'])
    ->name('admin.stakeholders.duplicate');
Route::resource('/stakeholders', StakeholderController::class)
    ->except(['show'])
    ->names('admin.stakeholders');

Route::get('/expenses/export', [ExpenseController::class, 'export'])
    ->name('admin.expenses.export');
Route::post('/expenses/{expense}/duplicate', [ExpenseController::class, 'duplicate'])
    ->name('admin.expenses.duplicate');
Route::resource('/expenses', ExpenseController::class)
    ->except(['show'])
    ->names('admin.expenses');

Route::post('/incomes/{income}/duplicate', [IncomeController::class, 'duplicate'])
    ->name('admin.incomes.duplicate');
Route::resource('/incomes', IncomeController::class)
    ->except(['show'])
    ->names('admin.incomes');

Route::resource('/posts', PostController::class)
    ->except(['show'])
    ->names('admin.posts');
Route::post('/posts/upload-image', [PostController::class, 'uploadImage'])->name('admin.posts.upload-image');
Route::get('/media/library', [MediaController::class, 'library'])->name('admin.media.library');
Route::post('/media/upload-json', [MediaController::class, 'uploadJson'])->name('admin.media.upload-json');
Route::resource('/media', MediaController::class)
    ->only(['index', 'store', 'destroy'])
    ->names('admin.media')
    ->parameters(['media' => 'media']);

Route::get('/nodes/graph', [NodeController::class, 'graph'])->name('admin.nodes.graph');
Route::resource('/nodes', NodeController::class)
    ->except(['show'])
    ->names('admin.nodes');

Route::resource('/node-connections', NodeConnectionController::class)
    ->except(['show'])
    ->parameters(['node-connections' => 'nodeConnection'])
    ->names('admin.node-connections');

Route::resource('/adages', AdageController::class)
    ->except(['show'])
    ->names('admin.adages');

Route::get('/timeline/visualize', [TimelineController::class, 'visualize'])->name('admin.timeline.visualize');
Route::resource('/timeline', TimelineController::class)
    ->except(['show'])
    ->names('admin.timeline');

Route::get('/subscribers', [SubscriberController::class, 'index'])->name('admin.subscribers.index');
Route::get('/subscribers/export', [SubscriberController::class, 'export'])->name('admin.subscribers.export');
Route::post('/subscribers/{subscriber}/unsubscribe', [SubscriberController::class, 'unsubscribe'])->name('admin.subscribers.unsubscribe');
Route::delete('/subscribers/{subscriber}', [SubscriberController::class, 'destroy'])->name('admin.subscribers.destroy');

Route::get('/people/search', [PersonController::class, 'search'])->name('admin.people.search');
Route::get('/people/{person}', [PersonController::class, 'show'])->whereNumber('person')->name('admin.people.show');
Route::get('/people/{person}/graph', [PersonController::class, 'showGraph'])->name('admin.people.graph');
Route::resource('/people', PersonController::class)
    ->except(['show'])
    ->names('admin.people');

Route::get('/interactions/women-in-circle', [InteractionController::class, 'womenInCircle'])
    ->name('admin.interactions.women-in-circle');
Route::post('/interactions/{interaction}/duplicate', [InteractionController::class, 'duplicate'])
    ->name('admin.interactions.duplicate');
Route::resource('/interactions', InteractionController::class)
    ->except(['show'])
    ->names('admin.interactions');

Route::get('/comments', [CommentController::class, 'index'])->name('admin.comments.index');
Route::put('/comments/{comment}/approve', [CommentController::class, 'approve'])->name('admin.comments.approve');
Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('admin.comments.destroy');

Route::controller(DictionaryController::class)
    ->prefix('/dictionaries/{table}')
    ->whereIn('table', array_column(DictionaryController::navigation(), 'table'))
    ->group(function () {
        Route::get('/', 'index')->name('admin.dictionaries.index');
        Route::post('/', 'store')->name('admin.dictionaries.store');
        Route::put('/{record}', 'update')->whereNumber('record')->name('admin.dictionaries.update');
        Route::delete('/{record}', 'destroy')->whereNumber('record')->name('admin.dictionaries.destroy');
    });
