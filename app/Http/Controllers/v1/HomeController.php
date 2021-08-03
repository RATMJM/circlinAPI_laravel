<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function town(Request $request):array
    {
        $mc = new MissionController();

        $bookmark = $mc->get_bookmark($request)['data']['missions'];
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
        $mission = $mc->missions($request, 3);

        return success([
            'success' => true,
            'bookmarks' => $bookmark,
            'banners' => $banner,
            'missions' => $mission,
        ]);
    }
}
