<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\v1_1\ScheduleController;
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

Route::get('/test', [ScheduleController::class, 'sendReservedPush']);

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/login', [Admin\AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login']);
    Route::get('/logout', [Admin\AuthController::class, 'logout'])->name('logout');

    Route::group(['middleware' => ['web', 'admin']], function () {
        // 통계
        Route::get('/', [Admin\UserController::class, 'index'])->name('index');
        Route::get('/user', [Admin\UserController::class, 'index'])->name('user.index');
        Route::get('/order', [Admin\OrderController::class, 'index'])->name('order.index');
        Route::group(['prefix' => '/mission', 'as' => 'mission.'], function () {
            Route::get('/', [Admin\MissionController::class, 'index'])->name('index');
            Route::group(['prefix' => '/{mission_id}'], function () {
                Route::get('/', [Admin\MissionController::class, 'show'])->name('show');
                Route::resource('/notice', Admin\MissionNoticeController::class);
                // Route::get('/notice', [Admin\MissionNoticeController::class, 'index'])->name('mission.notice.index');
                // Route::get('/notice/{id}', [Admin\MissionNoticeController::class, 'show'])->name('mission.notice.show');
            });
        });
        Route::get('/feed', [Admin\FeedController::class, 'index'])->name('feed.index');

        Route::get('/banner', fn() => redirect()->route('admin.banner.index', ['type' => 'float']))->name("banner");
        Route::get('/banner/log', [Admin\BannerLogController::class, 'index'])->name('banner.log.index');
        Route::get('/banner/log/{id}', [Admin\BannerLogController::class, 'show'])->name('banner.log.show');
        Route::group(['prefix' => '/banner/{type}', 'as' => 'banner.'], function () {
            Route::get('/', [Admin\BannerController::class, 'index'])->name('index');
            Route::get('/edit', [Admin\BannerController::class, 'editAll'])->name('edit.all');
            Route::put('/', [Admin\BannerController::class, 'updateAll'])->name('update.all');
            Route::get('/{id}', [Admin\BannerController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [Admin\BannerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [Admin\BannerController::class, 'update'])->name('update');
            Route::delete('/{id}', [Admin\BannerController::class, 'destroy'])->name('destroy');
        });

        // 관리
        Route::resource('/notice', Admin\NoticeController::class);
        Route::patch('/notice/{notice}/show', [Admin\NoticeController::class, 'update_show'])
            ->name('notice.update_show');

        Route::get('/push/reservation', [Admin\PushController::class, 'index'])->name('push.reservation');
        Route::get('/push/history', [Admin\PushController::class, 'history'])->name('push.history');
        Route::resource('/push', Admin\PushController::class);
    });

    // 외부 제공 어드민
    Route::get('/world-vision2-hiking', [Admin\EtcController::class, 'world_vision2_hiking'])
        ->name('world_vision2_hiking');
});
