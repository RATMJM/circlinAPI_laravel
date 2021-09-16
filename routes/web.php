<?php

use App\Http\Controllers\Admin;
use App\Models\DeleteUser;
use App\Models\User;
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
    Route::get('/', function (Request $request) {
        return $request->ip();
    });

    Route::get('/login', [Admin\AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login']);
    Route::get('/logout', [Admin\AuthController::class, 'logout'])->name('logout');

    Route::group(['middleware' => ['web', 'admin']], function () {
        Route::get('/user', [Admin\UserController::class, 'index'])->name('user.index');
        Route::get('/order', [Admin\OrderController::class, 'index'])->name('order.index');
    });
});
