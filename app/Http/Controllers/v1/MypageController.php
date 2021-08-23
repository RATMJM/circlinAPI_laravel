<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\UserWallpaper;
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
    public function feed(Request $request): array
    {
        return (new UserController())->feed($request, token()->uid);
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

    /**
     * 나를 팔로우
     */
    public function follower(): array
    {
        return (new UserController())->follower(token()->uid);
    }

    /**
     * 내가 팔로우
     */
    public function following(): array
    {
        return (new UserController())->following(token()->uid);
    }

    public function wallpaper(): array
    {
        $user_id = token()->uid;

        $data = UserWallpaper::where('user_id', $user_id)
            ->select(['image', 'thumbnail_image'])
            ->orderBy('id', 'desc')
            ->get();

        return success([
            'result' => true,
            'wallpapers' => $data,
        ]);
    }
}
