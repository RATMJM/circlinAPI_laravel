<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function town(Request $request, $category_id = null): array
    {
        $category_id = $category_id ?? $request->get('category_id');

        if (!$category_id) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        $bookmark = (new BookmarkController())->index($request, 3)['data']['missions'];
        $banners = (new BannerController())->category_banner($category_id);
        $mission_total = Mission::where('mission_category_id', $category_id)->count();
        $missions = (new MissionCategoryController())->mission($request, $category_id, 3)['data']['missions'];

        return success([
            'result' => true,
            'bookmarks' => $bookmark,
            'banners' => $banners,
            'mission_total' => $mission_total,
            'missions' => $missions,
        ]);
    }

    public function badge(): array
    {
         $user_id = token()->uid;

        return success([
            'result' => true,
            'feeds' => random_int(0,50),
            'missions' => random_int(0,5),
            'notifies' => random_int(0,50),
            'messages' => random_int(0,200),
        ]);
    }
}
