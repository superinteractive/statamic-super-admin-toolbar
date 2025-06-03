<?php

use Illuminate\Support\Facades\Route;
use SuperInteractive\SuperAdminToolbar\Http\Controllers\SuperAdminToolbarController;

Route::get('super-admin-toolbar', SuperAdminToolbarController::class)->name('super-admin-toolbar');
