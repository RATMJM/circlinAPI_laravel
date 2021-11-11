<?php

use App\Http\Controllers\AdminApi;
use App\Http\Middleware\ApiCheckAdmin;
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

/* auth */
Route::post('/auth/login', [adminApi\AuthController::class, 'login'])->name('auth.login');
Route::post('/auth/logout', [adminApi\AuthController::class, 'logout'])->name('auth.logout');
Route::get('/auth/my', [adminApi\AuthController::class, 'my'])->name('auth.my');

Route::middleware(ApiCheckAdmin::class)->group(function () {
    Route::get('/banner/log', [AdminApi\BannerLogController::class, 'index'])->name('banner.log.index');
    Route::get('/banner/log/{id}', [AdminApi\BannerLogController::class, 'show'])->name('banner.log.show');
    Route::get('/banner/log/{id}/log', [AdminApi\BannerLogController::class, 'log'])->name('banner.log.log');
});
