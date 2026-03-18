<?php

use Illuminate\Support\Facades\Route;

Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
Route::view('/reports', 'admin.reports')->name('admin.reports');
Route::view('/settings', 'admin.settings')->name('admin.settings');
