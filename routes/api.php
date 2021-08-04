<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\v1;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return ['success' => true];
});

/* 인증 */
Route::group(['prefix' => 'auth'], function () {
    /* 중복 체크 */
    Route::group(['prefix' => 'exists'], function () {
        Route::get('/email/{email}', [v1\AuthController::class, 'exists_email']);
        Route::get('/nickname/{nickname}', [v1\AuthController::class, 'exists_nickname']);
    });

    /* 회원가입 */
    Route::post('/signup', [v1\AuthController::class, 'signup']);
    Route::post('/signup/sns', [v1\AuthController::class, 'signup_sns']);

    /* 로그인 */
    Route::post('/login', [v1\AuthController::class, 'login']);
    Route::post('/login/sns', [v1\AuthController::class, 'login_sns']);

    /* 초기데이터 구성 */
    Route::get('/check/init', [v1\AuthController::class, 'check_init']);
});

/* 유저 관련 */
Route::group(['prefix' => 'user'], function () {
    Route::get('/', [v1\UserController::class, 'user']);
    Route::patch('/profile', [v1\UserController::class, 'update']);
    Route::patch('/profile/image', [v1\UserController::class, 'change_profile_image']);
    Route::delete('/profile/image', [v1\UserController::class, 'remove_profile_image']);
    Route::resource('favorite_category',v1\UserFavoriteCategoryController::class);
    Route::get('/follower', [v1\UserController::class, 'follower']);
    Route::get('/following', [v1\UserController::class, 'following']);
    Route::post('/follow', [v1\UserController::class, 'follow']);
    Route::delete('/follow/{id}', [v1\UserController::class, 'unfollow']);
});
Route::get('/change_profile_image', [v1\UserController::class, 'change_profile_image']);
Route::get('/area', [v1\BaseController::class, 'area']);
Route::get('/suggest_user', [v1\BaseController::class, 'suggest_user']);

/* Home */
Route::get('/town', [v1\HomeController::class, 'town']);

/* 미션 관련 */
Route::resources([
    'category' => v1\MissionCategoryController::class,
    'mission' => v1\MissionController::class,
    'bookmark' => v1\BookmarkController::class,
]);

Route::group(['prefix' => 'feed'], function () {
    //
});

Route::group(['prefix' => 'mypage'], function () {
    Route::get('/', [v1\MypageController::class, 'mypage']);
});
