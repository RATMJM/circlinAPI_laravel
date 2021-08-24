<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\FeedImage;
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
        return (new UserController())->wallpaper(token()->uid);
    }

    public function gallery(Request $request): array
    {
        $user_id = token()->uid;
        $page = $request->get('page', 0);
        $limit = $request->get('limit', 20);

        $data = FeedImage::where('feeds.user_id', $user_id)
            ->join('feeds', 'feeds.id', 'feed_images.feed_id')
            ->select([
                'feeds.id as feed_id',
                'feed_images.type', 'feed_images.image',
            ])
            ->orderBy('feed_images.id', 'desc')
            ->orderBy('order', 'desc')
            ->skip($page * $limit)
            ->take($limit)
            ->get();

        return success([
            'result' => true,
            'images' => $data,
        ]);
    }
}
