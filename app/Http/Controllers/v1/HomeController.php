<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function town(Request $request):array
    {
        $id = $request->get('category_id');

        if (!$id) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        $bookmark = (new BookmarkController())->index($request, 3)['data']['missions'];
        $banner = [
            [
                'banner_image' => 'https://via.placeholder.com/1500x750',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://via.placeholder.com/1500x750',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://via.placeholder.com/1500x750',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://via.placeholder.com/1500x750',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://via.placeholder.com/1500x750',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
        ]; // 더미데이터
        $mission_total = Mission::where('mission_category_id', $id)->count();
        $mission = (new MissionCategoryController())->show($request, $id, 3)['data']['missions'];

        return success([
            'success' => true,
            'bookmarks' => $bookmark,
            'banners' => $banner,
            'missions' => ['total' => $mission_total, 'missions' => $mission],
        ]);
    }
}
