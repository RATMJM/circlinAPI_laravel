<?php

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

Route::get('/user/profile/image', function () {
    echo "<form method='post' action='/v1/user/profile/image' enctype='multipart/form-data'>
    <input type='file' name='file' id='file'>
    <button type='submit'>업로드</button>
</form>";
});

Route::get('/feed', function () {
    echo "<form method='post' action='/v1/feed' enctype='multipart/form-data'>
    <textarea name='content' cols='30' rows='10'></textarea><br>
    <input type='checkbox' name='missions[]' checked value='1'>
    <input type='checkbox' name='missions[]' checked value='20'>
    <input type='checkbox' name='missions[]' checked value='40'>
    <br>
    <input type='file' name='files[]' multiple><br>
    <button type='submit'>업로드</button>
</form>";
});
