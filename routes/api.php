<?php

use App\Http\Controllers\v1;
use Illuminate\Support\Facades\Route;

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
    Route::post('/profile/image', [v1\UserController::class, 'change_profile_image'])->name('profile.update.image');
    Route::delete('/profile/image', [v1\UserController::class, 'remove_profile_image'])->name('profile.delete.image');
    Route::post('/profile/token', [v1\UserController::class, 'update_token'])->name('profile.update.token');
    Route::resource('favorite_category', v1\UserFavoriteCategoryController::class);
    Route::post('/follow', [v1\UserController::class, 'follow'])->name('follow.create');
    Route::delete('/follow/{id}', [v1\UserController::class, 'unfollow'])->name('follow.delete');

    /* 유저 상세 페이지 */
    Route::group(['prefix' => '{user_id}'], function () {
        Route::get('/', [v1\UserController::class, 'show'])->name('show');
        Route::get('/feed', [v1\UserController::class, 'feed'])->name('feed');
        Route::get('/check', [v1\UserController::class, 'check'])->name('check');
        Route::get('/mission', [v1\UserController::class, 'mission'])->name('mission');
        Route::get('/mission/created', [v1\UserController::class, 'created_mission'])->name('mission.created');
        Route::get('/follower', [v1\UserController::class, 'follower'])->name('follower');
        Route::get('/following', [v1\UserController::class, 'following'])->name('following');
    });
});
Route::get('/area', [v1\BaseController::class, 'area'])->name('area');
Route::get('/suggest_user', [v1\BaseController::class, 'suggest_user'])->name('suggest.user');

/* 알림 관련 */
Route::get('/notification', [v1\NotificationController::class, 'index'])->name('notification.index');

/* 미션 관련 */
Route::get('/bookmark', [v1\BookmarkController::class, 'index'])->name('bookmark.index');
Route::post('/bookmark', [v1\BookmarkController::class, 'store'])->name('bookmark.store');
Route::delete('/bookmark/{mission_id}', [v1\BookmarkController::class, 'destroy'])->name('bookmark.destroy');
Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
    Route::get('/{town?}', [v1\MissionCategoryController::class, 'index'])->where(['town' => 'town'])->name('index');
    Route::get('/{category_id}', [v1\MissionCategoryController::class, 'show'])->name('show');
    Route::get('/{category_id}/mission', [v1\MissionCategoryController::class, 'mission'])->name('mission');
    Route::get('/{category_id}/user', [v1\MissionCategoryController::class, 'user'])->name('user');
});
Route::group(['prefix' => 'mission', 'as' => 'mission.'], function () {
    Route::post('/', [v1\MissionController::class, 'store'])->name('store');
    Route::group(['prefix' => '{mission_id}'], function () {
        Route::get('/', [v1\MissionController::class, 'show'])->name('show');
        Route::get('/user', [v1\MissionController::class, 'user'])->name('user');
        Route::post('/invite', [v1\MissionController::class, 'invite'])->name('invite');
        Route::get('/like', [v1\MissionLikeController::class, 'index'])->name('like.index');
        Route::post('/like', [v1\MissionLikeController::class, 'store'])->name('like.store');
        Route::delete('/like', [v1\MissionLikeController::class, 'destroy'])->name('like.destroy');
        Route::get('/comment', [v1\MissionCommentController::class, 'index'])->name('comment.index');
        Route::post('/comment', [v1\MissionCommentController::class, 'store'])->name('comment.store');
        Route::delete('/comment/{comment_id}', [v1\MissionCommentController::class, 'destroy'])->name('comment.destroy');
    });
});

/* Home */
Route::get('/town', [v1\HomeController::class, 'town'])->name('home.town');
Route::get('/newsfeed', [v1\HomeController::class, 'newsfeed'])->name('home.newsfeed');
Route::get('/badge', [v1\HomeController::class, 'badge'])->name('home.badge');

Route::group(['prefix' => 'feed', 'feed.'], function () {
    Route::post('/', [v1\FeedController::class, 'store'])->name('store');
    Route::group(['prefix' => '{feed_id}'], function () {
        Route::get('/', [v1\FeedController::class, 'show'])->name('show');
        Route::post('/show', [v1\FeedController::class, 'show_feed'])->name('show');
        Route::post('/hide', [v1\FeedController::class, 'hide_feed'])->name('hide');
        Route::delete('/', [v1\FeedController::class, 'destroy'])->name('destroy');
        Route::get('/like', [v1\FeedLikeController::class, 'index'])->name('like.index');
        Route::post('/like', [v1\FeedLikeController::class, 'store'])->name('like.store');
        Route::delete('/like', [v1\FeedLikeController::class, 'destroy'])->name('like.destroy');
        Route::get('/comment', [v1\FeedCommentController::class, 'index'])->name('comment.index');
        Route::post('/comment', [v1\FeedCommentController::class, 'store'])->name('comment.store');
        Route::delete('/comment/{comment_id}', [v1\FeedCommentController::class, 'destroy'])->name('comment.destroy');
    });
});

/* 마이페이지 (UserController 로 넘김) */
Route::group(['prefix' => 'mypage', 'as' => 'mypage.'], function () {
    Route::get('/', [v1\MypageController::class, 'index'])->name('index');
    Route::get('/feed/{feed_id?}', [v1\MypageController::class, 'feed'])->name('feed');
    Route::get('/check', [v1\MypageController::class, 'check'])->name('check');
    Route::get('/mission', [v1\MypageController::class, 'mission'])->name('mission');
    Route::get('/mission/created', [v1\MypageController::class, 'created_mission'])->name('mission.created');
    Route::get('/follower', [v1\MypageController::class, 'follower'])->name('follower');
    Route::get('/following', [v1\MypageController::class, 'following'])->name('following');
});

/* 탐색 페이지 */
Route::get('/explore', [v1\SearchController::class, 'index'])->name('explore');
Route::get('/explore/search', [v1\SearchController::class, 'search'])->name('explore.search');
Route::get('/explore/search/user', [v1\SearchController::class, 'user'])->name('explore.search.user');
Route::get('/explore/search/mission', [v1\SearchController::class, 'mission'])->name('explore.search.mission');

/* 채팅 관련 */
Route::group(['prefix' => 'chat', 'as' => 'chat.'], function () {
    Route::get('/', [v1\ChatController::class, 'index'])->name('index');
    Route::get('/{room_id}', [v1\ChatController::class, 'show'])->name('show');
    Route::post('/{room_id}/send', [v1\ChatController::class, 'send_message'])->name('send');
    Route::get('/{room_id}/user', [v1\ChatController::class, 'user'])->name('user');
    Route::post('/{room_id}/leave', [v1\ChatController::class, 'leave_room'])->name('leave');
    Route::post('/{room_id}/show', [v1\ChatController::class, 'show_room'])->name('show');
    Route::post('/{room_id}/hide', [v1\ChatController::class, 'hide_room'])->name('hide');
    // Route::post('/direct/room/{target_id}', [v1\ChatController::class, 'create_or_enter_room'])->name('direct.enter');
    Route::post('/direct/send/{target_id}', [v1\ChatController::class, 'send_direct'])->name('direct.send');
});

/* 샵 관련 */
Route::get('/shop_banner', [v1\ShopController::class, 'shop_banner']);
Route::get('/shop_category', [v1\ShopController::class, 'shop_category']);
Route::post('/item_list', [v1\ShopController::class, 'item_list']);
Route::get('/shop/point', [v1\ShopController::class, 'shop_point_list']);
Route::get('/shop/bought', [v1\ShopController::class, 'bought_product_list']);
Route::get('/shop/cart', [v1\ShopController::class, 'cart_list']);
