<?php

use Illuminate\Support\Facades\Route;
use SuperInteractive\SuperAdminToolbar\Http\Controllers\SuperAdminToolbarController;
use SuperInteractive\SuperAdminToolbar\Http\Controllers\EntryCacheController;

Route::get('super-admin-toolbar', SuperAdminToolbarController::class)->name('super-admin-toolbar');
Route::post('super-admin-toolbar/entry-cache/refresh', [EntryCacheController::class, 'refresh'])->name('super-admin-toolbar.entry-cache.refresh');
