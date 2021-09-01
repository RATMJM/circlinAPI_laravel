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

        /*$data = FeedImage::where('feeds.user_id', $user_id)
            ->join('feeds', 'feeds.id', 'feed_images.feed_id')
            ->select([
                'feeds.id as feed_id',
                'feed_images.type', 'feed_images.image',
            ])
            ->orderBy('feed_images.id', 'desc')
            ->orderBy('order', 'desc')
            ->skip($page * $limit)
            ->take($limit)
            ->get();*/

        $data = [
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/bike01.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/bike02.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/bike03.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/bike04.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/bike05.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/fitness01.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/fitness02.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/fitness03.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/fitness04.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/fitness05.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/fitness06.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/fitness07.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/fitness08.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/img01.JPG'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/img02.JPG'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/img03.JPG'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/img04.JPG'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/img05.JPG'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/img06.JPG'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/img07.JPG'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/img08.JPG'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/img09.JPG'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/img10.JPG'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/rollerskate01.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/run01.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/run02.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/run03.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/run04.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/run05.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/walk01.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/walk02.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/walk03.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/walk04.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/walk05.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/walk06.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/yoga01.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/yoga02.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/yoga03.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/yoga04.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/yoga05.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/yoga06.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/yoga07.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/yoga08.jpg'],
            ['type' => 'image', 'image' => 'https://cyld20183.speedgabia.com/Image/GALLERY/yoga09.jpg'],
        ];

        return success([
            'result' => true,
            'images' => $data,
        ]);
    }
}
