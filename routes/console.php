<?php

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

Artisan::command('test', function () {
    $data = [
    ];

    print("총 " . count($data) . "\n");

    $count = 0;
    foreach ($data as $i => $item) {
        print((++$count) . "개 완료\n");
    }
})->describe('테스트');
