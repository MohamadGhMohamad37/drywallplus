<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;

Route::group([
        'prefix' => '{lang}/admin',
    'where' => ['lang' => 'en|ar'],
    'middleware' => ['setLocale'],
], function () {
Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard.admin');
Route::resource('categories', CategoryController::class);

});