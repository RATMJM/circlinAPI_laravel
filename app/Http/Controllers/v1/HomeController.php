<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\MissionStat;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class HomeController extends Controller
{
    public function town(Request $request, $category_id = null): array
    {
        $category_id = $category_id ?? $request->get('category_id');

        $category_id = Arr::wrap($category_id ??
            Arr::pluck(($categories = (new MissionCategoryController())->index('town')['data']['categories']), 'id'));

        $tabs = [];
        foreach ($category_id as $id) {
            $tmp = $id === 0 ? $category_id : $id;
            $tabs[$id] = [
                'bookmark' => (new BookmarkController())->index($request, $id, 3)['data']['missions'],
                'banners' => (new BannerController())->category_banner($id),
                'mission_total' => Mission::whereNull('ended_at')->orWhere('ended_at', '>', date('Y-m-d H:i:s'))
                ->when($tmp, function ($query, $id) {
                    $query->whereIn('mission_category_id', Arr::wrap($id));
                })->count(),
                'missions' => (new MissionCategoryController())->mission($request, $tmp, 3)['data']['missions'],
            ];
            break;
        }

        if (count($category_id) > 1) {
            return success(['result' => true, 'categories' => $categories ?? [], 'tabs' => $tabs]);
        } else {
            return success(['result' => true, 'tabs' => $tabs[$category_id[0]]]);
        }
    }

    public function newsfeed(): array
    {

    }

    public function badge(): array
    {
        $user_id = token()->uid;

        return success([
            'result' => true,
            'feeds' => random_int(0, 50),
            'missions' => MissionStat::where('user_id', $user_id)
                ->whereDoesntHave('feed_missions', function ($query) {
                    $query->where('created_at', '>=', date('Y-m-d', time()));
                })->count(),
            'notifies' => random_int(0, 50),
            'messages' => random_int(0, 200),
        ]);
    }
}
