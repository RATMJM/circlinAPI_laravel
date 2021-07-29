<?php

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

    /* 로그인 */
    Route::post('/login', 'AuthController@login');
    Route::post('/login/sns', 'AuthController@login_sns');

    /* 초기데이터 구성 */
    Route::get('/check/init/{user_id}', 'AuthController@check_init');
});

Route::group(['prefix' => 'user'], function () {
    Route::patch('{user_id}/profile', 'UserController@update_profile');
    Route::post('{user_id}/favorite_category', 'UserController@add_favorite_category');
    Route::delete('{user_id}/favorite_category', 'UserController@remove_favorite_category');
    Route::post('{user_id}/follow/{target_id}', 'UserController@update_area');
    Route::delete('{user_id}/unfollow/{target_id}', 'UserController@update_area');
});

Route::get('area', function (Request $request) {

});
