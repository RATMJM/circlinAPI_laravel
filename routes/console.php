<?php

use App\Http\Controllers\v1_1\PushController;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:sort', function () {
    \App\Http\Controllers\v1\ScheduleController::sort_users($this);
})->describe('유저 추천 로직 갱신');

Artisan::command('user:point {id} {point}', function ($id, $point) {
    \App\Http\Controllers\v1_1\PointController::change_point($id, $point, 'test');
})->describe('포인트 지급');

Artisan::command('order:cancel {order_id}', function ($order_id) {
    $data = \App\Models\Order::where('id', $order_id)->get();

    print("총 " . $data->count() . "\n");

    $count = 0;
    foreach ($data as $i => $item) {
        \Illuminate\Support\Facades\DB::beginTransaction();
        \App\Http\Controllers\v1_1\PointController::change_point($item->user_id, $item->use_point, 'order_cancel', 'order', $item->id);
        $item->delete();
        \Illuminate\Support\Facades\DB::commit();
        print((++$count) . "개 완료\n");
    }
})->describe('주문취소');

Artisan::command('push:all {msg}', function ($msg) {
    if (trim($msg) == '') {
        print('메시지가 없습니다');
        return false;
    }
    print("푸시 내용 : $msg\n");
    $users = User::pluck('id');
    print("총 " . $users->count() . "\n");
    $tmp = [];
    $count = 0;
    foreach ($users as $user) {
        $tmp[] = $user;
        if (count($tmp) >= 1000) {
            PushController::gcm_notify($tmp, '써클인', $msg, '');
            $tmp = [];
            print((++$count * 1000) . "개 완료\n");
        }
    }
    PushController::gcm_notify($tmp, '써클인', $msg, '');
    print((++$count * 1000) . "개 완료\n");
});

Artisan::command('test', function () {
    $data = [
    ];

    print("총 " . count($data) . "\n");

    $count = 0;
    foreach ($data as $i => $item) {
        print((++$count) . "개 완료\n");
    }
})->describe('테스트');
