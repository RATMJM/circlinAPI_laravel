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
    <input type='text' name='is_hidden' value='0'><br>
    <input type='checkbox' name='missions[]' checked value='1'>
    <input type='checkbox' name='missions[]' checked value='20'>
    <input type='checkbox' name='missions[]' checked value='40'>
    <br>
    <input type='text' name='product_brand' value='나이키'>
    <input type='text' name='product_title' value='나이키 에어맥스 GENOME'>
    <input type='text' name='product_price' value='199000'>
    <input type='text' name='product_url' value='https://google.com/search?q=나이키 에어맥스 GENOME'>
    <br>
    <input type='text' name='place_address' value='서울시 동대문구 전농로 21'>
    <input type='text' name='place_title' value='우리집'>
    <input type='text' name='place_description' value='집'>
    <input type='text' name='place_image' value='https://cyld20182.speedgabia.com/Image/profile/64175/3bNXkQOqRxj8t25Zf3Y2yCuxpDgK4zniuwtTHRaB.png'>
    <input type='text' name='place_url' value='https://google.com/search?q=우리집'>
    <br>
    <input type='file' name='files[]' multiple><br>
    <button type='submit'>업로드</button>
</form>";
});

Route::get('/mission', function () {
    echo "<form method='post' action='/v1/mission' enctype='multipart/form-data'>
    <input type='hidden' name='mission_category_id' value='12'>
    <textarea name='title' cols='30' rows='10'></textarea><br>
    <input type='text' name='product_brand' value='농심'>
    <input type='text' name='product_title' value='신라면'>
    <input type='text' name='product_price' value='850'>
    <input type='text' name='product_url' value='https://naver.com/'>
    <br>
    <input type='text' name='place_address' value='서울시 동대문구 전농로 21'>
    <input type='text' name='place_title' value='우리집'>
    <input type='text' name='place_description' value='집'>
    <input type='text' name='place_image' value='https://cyld20182.speedgabia.com/Image/profile/64175/3bNXkQOqRxj8t25Zf3Y2yCuxpDgK4zniuwtTHRaB.png'>
    <input type='text' name='place_url' value='https://google.com/search?q=우리집'>
    <br>
    <input type='file' name='thumbnail'>
    <input type='file' name='files[]' multiple><br>
    <button type='submit'>업로드</button>
</form>";
});
