<?php

use App\Http\Controllers\AdminApi;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/left-tab-items', [AdminApi\BaseController::class, 'leftTabItems'])->name('left-tab-items');
Route::get('/banner/log/{type}', [AdminApi\BannerLogController::class, 'index'])
    ->where(['type' => '(float|local|shop)'])->name('banner.log.index');
Route::get('/banner/log/{id}', [AdminApi\BannerLogController::class, 'show'])
    ->where(['id' => '[\d]+'])->name('banner.log.show');
