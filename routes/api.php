<?php

use App\Http\Controllers\Admin\StakeholderController;
use App\Http\Controllers\PublicSite\BlogController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['ok' => true]));
Route::post('/posts/{slug}/view', [BlogController::class, 'trackView'])->name('api.posts.view');

Route::middleware(['web', 'auth', 'admin'])->group(function (): void {
	Route::get('/stakeholders/lookup', [StakeholderController::class, 'lookup'])->name('api.stakeholders.lookup');
	Route::post('/stakeholders/quick', [StakeholderController::class, 'quickStore'])->name('api.stakeholders.quick');
});
