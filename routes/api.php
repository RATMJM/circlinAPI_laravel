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
})->name('index');

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
    Route::post('/profile/image', [v1\UserController::class, 'change_profile_image'])->name('profile.image.update');
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
        Route::get('/mission/created', [v1\UserController::class, 'created_mission'])->name('mission.created');
    });
});
Route::post('/change_profile_image', [v1\UserController::class, 'change_profile_image']);
Route::get('/area', [v1\BaseController::class, 'area'])->name('area');
Route::get('/suggest_user', [v1\BaseController::class, 'suggest_user'])->name('suggest.user');

/* 알림 관련 */
Route::get('/notification', [v1\NotificationController::class, 'index'])->name('notification.index');

/* 미션 관련 */
Route::resources([
    'bookmark' => v1\BookmarkController::class,
]);
Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
    Route::get('/{town?}', [v1\MissionCategoryController::class, 'index'])->where(['town' => 'town'])->name('index');
    Route::get('/{category_id}', [v1\MissionCategoryController::class, 'show'])->name('show');
    Route::get('/{category_id}/mission', [v1\MissionCategoryController::class, 'mission'])->name('mission');
});
Route::group(['prefix' => 'mission', 'as' => 'mission.'], function () {
    Route::get('/{mission_id}', [v1\MissionController::class, 'show'])->name('show');
    Route::get('/{mission_id}/user', [v1\MissionController::class, 'user'])->name('user');
});

/* Home */
Route::get('/town', [v1\HomeController::class, 'town'])->name('home.town');
Route::get('/badge', [v1\HomeController::class, 'badge'])->name('home.badge');

Route::resource('/feed', v1\FeedController::class);

/* 마이페이지 (UserController 로 넘김) */
Route::group(['prefix' => 'mypage', 'as' => 'mypage.'], function () {
    Route::get('/', [v1\MypageController::class, 'index'])->name('index');
    Route::get('/feed/{feed_id?}', [v1\MypageController::class, 'feed'])->name('feed');
    Route::get('/check', [v1\MypageController::class, 'check'])->name('check');
    Route::get('/mission', [v1\MypageController::class, 'mission'])->name('mission');
    Route::get('/mission/created', [v1\MypageController::class, 'created_mission'])->name('mission.created');
});

/* 탐색 페이지 */
Route::get('explore', [v1\SearchController::class, 'index'])->name('explore');

/* 피드 이미지, 동영상 업로드 관련*/
Route::post('/feed_upload', [v1\FeedController::class, 'feed_upload']);
Route::get('/compress', [v1\UserController::class, 'compress'])->name('compress');

/* 샵 관련 */
Route::get('/shop_banner', [v1\ShopController::class, 'shop_banner']);
Route::get('/shop_category', [v1\ShopController::class, 'shop_category']);
Route::post('/item_list', [v1\ShopController::class, 'item_list']);
Route::get('/shop/point', [v1\ShopController::class, 'shop_point_list']);
 
