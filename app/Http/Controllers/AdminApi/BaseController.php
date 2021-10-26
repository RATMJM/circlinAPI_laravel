<?php

namespace App\Http\Controllers\AdminApi;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function leftTabItems(): array
    {
        $data = [
            ['url' => '/user', 'primary' => '유저 통계', 'name' => 'chart-bar'],
            ['url' => '/order', 'primary' => '주문 통계', 'name' => 'chart-bar'],
            ['url' => '/mission', 'primary' => '미션 통계', 'name' => 'chart-bar'],
            ['url' => '/feed', 'primary' => '피드 통계', 'name' => 'chart-bar'],
            ['url' => '/banner', 'primary' => '배너 클릭률 통계', 'name' => 'chart-bar'],
            [],
            ['url' => '/notice', 'primary' => '공지사항 관리', 'name' => 'tasks-alt'],
            ['url' => '/push', 'primary' => '푸시 관리', 'name' => 'tasks-alt'],
        ];

        return [
            'data' => $data,
        ];
    }
}
