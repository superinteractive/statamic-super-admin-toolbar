<?php

use Illuminate\Support\Facades\Route;
use SuperInteractive\SuperAdminToolbar\Http\Controllers\SuperAdminToolbarController;

Route::post('super-admin-toolbar', SuperAdminToolbarController::class)->name('super-admin-toolbar');
