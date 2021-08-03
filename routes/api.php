<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
        Route::get('/email/{email}', 'AuthController@exists_email');
        Route::get('/nickname/{nickname}', 'AuthController@exists_nickname');
    });

    /* 회원가입 */
    Route::post('/signup', 'AuthController@signup');
    Route::post('/signup/sns', 'AuthController@signup_sns');

    /* 로그인 */
    Route::post('/login', 'AuthController@login');
    Route::post('/login/sns', 'AuthController@login_sns');

    /* 초기데이터 구성 */
    Route::get('/check/init', 'AuthController@check_init');
});

/* 유저 관련 */
Route::group(['prefix' => 'user'], function () {
    Route::get('/', 'UserController@user');
    Route::patch('/profile', 'UserController@update_profile');
    Route::patch('/profile/image', 'UserController@change_profile_image');
    Route::delete('/profile/image', 'UserController@remove_profile_image');
    Route::get('/favorite_category', 'UserController@get_favorite_category');
    Route::post('/favorite_category', 'UserController@add_favorite_category');
    Route::delete('/favorite_category', 'UserController@remove_favorite_category');
    Route::get('/follower', 'UserController@follower');
    Route::get('/following', 'UserController@following');
    Route::post('/follow', 'UserController@follow');
    Route::delete('/follow', 'UserController@unfollow');
});
Route::get('/change_profile_image', 'UserController@change_profile_image');
Route::get('/area', 'BaseController@area');
Route::get('/suggest_user', 'BaseController@suggest_user');

/* Home */
Route::get('/town', 'HomeController@town');

/* 미션 관련 */
Route::group(['prefix' => 'mission'], function () {
    Route::get('/', 'MissionController@missions');
    Route::get('/{mission_id}', 'MissionController@mission')->where(['mission_id' => '\d+']);
    Route::get('/category', 'MissionController@categories');
    Route::get('/bookmark', 'MissionController@get_bookmark');
    Route::post('/bookmark', 'MissionController@add_bookmark');
    Route::delete('/bookmark', 'MissionController@remove_bookmark');
});

Route::group(['prefix' => 'feed'], function () {
    //
});
