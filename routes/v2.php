<?php

#region 유저 관련
use App\Http\Controllers\v2\UserController;

Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
    #region 유저 상세 페이지
    Route::group(['prefix' => '{user_id}'], function () {
        Route::get('/follower', [UserController::class, 'follower'])->name('follower');
        Route::get('/following', [UserController::class, 'following'])->name('following');
    });
    #endregion
});
#endregion
