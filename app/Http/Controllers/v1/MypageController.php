<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    /**
     * 내 프로필 기본 데이터
     * */
    public function index(): array
    {
        return (new UserController())->show(token()->uid);
    }

    /**
     * 내 피드 데이터
     */
    public function feed(Request $request, $feed_id = null): array
    {
        return (new UserController())->feed($request, token()->uid, $feed_id);
    }

    /**
     * 내가 체크한 피드
     * */
    public function check(Request $request): array
    {
        return (new UserController())->check($request, token()->uid);
    }

    /**
     * 내가 진행했던 미션 전체
     * */
    public function mission(Request $request): array
    {
        return (new UserController())->mission($request, token()->uid);
    }

    /**
     * 내가 제작한 미션
     */
    public function created_mission(Request $request): array
    {
        return (new UserController())->created_mission($request, token()->uid);
    }
}
