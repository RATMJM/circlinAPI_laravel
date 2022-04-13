<?php

namespace App\Http\Controllers\v1_1;

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
    public function follower(Request $request): array
    {
        return (new UserController())->follower($request, token()->uid);
    }

    /**
     * 내가 팔로우
     */
    public function following(Request $request): array
    {
        return (new UserController())->following($request, token()->uid);
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
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/bike01.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/bike02.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/bike03.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/bike04.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/bike05.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/fitness01.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/fitness02.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/fitness03.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/fitness04.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/fitness05.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/fitness06.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/fitness07.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/fitness08.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/img01.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/img02.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/img03.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/img04.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/img05.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/img06.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/img07.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/img08.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/img09.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/img10.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/rollerskate01.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/run01.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/run02.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/run03.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/run04.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/run05.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/walk01.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/walk02.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/walk03.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/walk04.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/walk05.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/walk06.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/yoga01.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/yoga02.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/yoga03.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/yoga04.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/yoga05.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/yoga06.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/yoga07.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/yoga08.png'],
            ['type' => 'image', 'image' => 'https://circlin-app.s3.ap-northeast-2.amazonaws.com/old/GALLERY/yoga09.png'],
        ];

        return success([
            'result' => true,
            'images' => $data,
        ]);
    }
}
