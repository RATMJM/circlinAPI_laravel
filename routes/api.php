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
Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
    /* 중복 체크 */
    Route::group(['prefix' => 'exists', 'as' => 'exists.'], function () {
        Route::get('/email/{email}', [v1\AuthController::class, 'exists_email'])->name('email');
        Route::get('/nickname/{nickname}', [v1\AuthController::class, 'exists_nickname'])->name('nickname');
    });

    /* 회원가입 */
    Route::post('/signup', [v1\AuthController::class, 'signup'])->name('signup');
    Route::post('/signup/sns', [v1\AuthController::class, 'signup_sns'])->name('signup.sns');

    /* 로그인 */
    Route::post('/login', [v1\AuthController::class, 'login'])->name('login');
    Route::post('/login/sns', [v1\AuthController::class, 'login_sns'])->name('login.sns');

    /* 초기데이터 구성 */
    Route::get('/check/init', [v1\AuthController::class, 'check_init'])->name('check.init');
});

/* 유저 관련 */
Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
    Route::get('/', [v1\UserController::class, 'index'])->name('index');
    Route::patch('/profile', [v1\UserController::class, 'update'])->name('profile.update');
    Route::patch('/profile/image', [v1\UserController::class, 'change_profile_image'])->name('profile.image.update');
    Route::delete('/profile/image', [v1\UserController::class, 'remove_profile_image'])->name('profile.image.delete');
    Route::resource('favorite_category',v1\UserFavoriteCategoryController::class);
    Route::get('/follower', [v1\UserController::class, 'follower'])->name('follower');
    Route::get('/following', [v1\UserController::class, 'following'])->name('following');
    Route::post('/follow', [v1\UserController::class, 'follow'])->name('follow.create');
    Route::delete('/follow/{id}', [v1\UserController::class, 'unfollow'])->name('follow.delete');

    /* 유저 상세 페이지 */
    Route::group(['prefix' => '{user_id}'], function () {
        Route::get('/', [v1\UserController::class, 'show'])->name('show');
        Route::get('/feed/{feed_id?}', [v1\UserController::class, 'feed'])->name('feed');
        Route::get('/check', [v1\UserController::class, 'check'])->name('check');
        Route::get('/mission', [v1\UserController::class, 'mission'])->name('mission');
    });
});
Route::post('/change_profile_image', [v1\UserController::class, 'change_profile_image']);
Route::get('/area', [v1\BaseController::class, 'area'])->name('area');
Route::get('/suggest_user', [v1\BaseController::class, 'suggest_user'])->name('suggest.user');

/* 미션 관련 */
Route::resources([
    'category' => v1\MissionCategoryController::class,
    'bookmark' => v1\BookmarkController::class,
]);
Route::group(['prefix' => 'mission'], function () {
    Route::get('/{mission_id}', [v1\MissionController::class, 'show']);
    Route::get('/{mission_id}/user', [v1\MissionController::class, 'user']);
});

/* Home */
Route::get('/town', [v1\HomeController::class, 'town'])->name('home.town');

Route::group(['prefix' => 'feed'], function () {
    //
});

/* 마이페이지 (UserController 로 넘김) */
Route::group(['prefix' => 'mypage', 'as' => 'mypage.'], function () {
    Route::get('/', [v1\MypageController::class, 'index'])->name('index');
    Route::get('/feed/{feed_id?}', [v1\MypageController::class, 'feed'])->name('feed');
    Route::get('/check', [v1\MypageController::class, 'check'])->name('check');
    Route::get('/mission', [v1\MypageController::class, 'mission'])->name('mission');
});

/* 탐색 페이지 */
Route::get('explore', [v1\SearchController::class, 'index']);
