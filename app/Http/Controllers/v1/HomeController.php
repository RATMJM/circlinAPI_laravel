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
            'https://via.placeholder.com/1500x750',
            'https://via.placeholder.com/1500x750',
            'https://via.placeholder.com/1500x750',
            'https://via.placeholder.com/1500x750',
            'https://via.placeholder.com/1500x750',
        ]; // 더미데이터
        $mission = $mc->get_mission($request, 3);

        return success([
            'success' => true,
            'bookmarks' => $bookmark,
            'banners' => $banner,
            'missions' => $mission,
        ]);
    }
}
