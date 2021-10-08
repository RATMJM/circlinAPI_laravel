<?php

use App\Http\Controllers\Admin;
use Illuminate\Http\Request;
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

Route::get('/', function (Request $request) {
    return $request->ip();
});

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/', [Admin\UserController::class, 'index'])->name('index');

    Route::get('/login', [Admin\AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login']);
    Route::get('/logout', [Admin\AuthController::class, 'logout'])->name('logout');

    Route::group(['middleware' => ['web', 'admin']], function () {
        // 통계
        Route::get('/user', [Admin\UserController::class, 'index'])->name('user.index');
        Route::get('/order', [Admin\OrderController::class, 'index'])->name('order.index');
        Route::get('/mission', [Admin\MissionController::class, 'index'])->name('mission.index');
        Route::get('/feed', [Admin\FeedController::class, 'index'])->name('feed.index');

        // 관리
        Route::resource('/notice', Admin\NoticeController::class);
        Route::patch('/notice/{notice}/show', [Admin\NoticeController::class, 'update_show'])->name('notice.update_show');
    });
});
