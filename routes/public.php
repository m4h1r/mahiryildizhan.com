<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.welcome');
})->name('home');

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    if (! in_array($locale, ['tr', 'en'], true)) {
        abort(404);
    }

    $request->session()->put('locale', $locale);

    return back();
})->name('locale.switch');
