<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function (): void {
    require __DIR__.'/admin.php';
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::group([], function (): void {
    require __DIR__.'/public.php';
});

Route::get('/theme/{scope}/{theme}', function (Request $request, string $scope, string $theme) {
    if (! in_array($scope, ['public', 'admin'], true)) {
        abort(404);
    }

    if (! in_array($theme, ['light', 'dark'], true)) {
        abort(404);
    }

    $request->session()->put('theme_'.$scope, $theme);

    return back();
})->name('theme.switch');

require __DIR__.'/auth.php';
