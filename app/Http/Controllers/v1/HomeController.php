<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\FeedComment;
use App\Models\FeedImage;
use App\Models\FeedLike;
use App\Models\FeedMission;
use App\Models\FeedPlace;
use App\Models\FeedProduct;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionCategory;
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
                'mission_total' => Mission::where(function ($query) {
                    $query->whereNull('ended_at')->orWhere('ended_at', '>', date('Y-m-d H:i:s'));
                })
                ->when($id, function ($query, $id) {
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

    public function newsfeed(Request $request): array
    {
        $user_id = token()->uid;

        $page = $request->get('page', 0);
        $limit = $request->get('limit', 5);

        $data = Feed::where('is_hidden', false)
            ->whereHas('user', function ($query) use ($user_id) {
                $query->whereHas('followers', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                });
            })
            ->join('users', 'users.id', 'feeds.user_id')
            ->join('user_stats', 'user_stats.user_id', 'users.id')
            ->select([
                'users.id as user_id', 'users.nickname', 'users.profile_image', 'area' => area(),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
                'feeds.id as feed_id', 'feeds.created_at', 'feeds.content',
                'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                'has_product' => FeedProduct::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 상품 있는지
                'has_place' => FeedPlace::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 위치 있는지
                'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('id')->limit(1),
                'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('id')->limit(1),
                'like_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                    ->where('user_id', token()->uid),
                'has_comment' => FeedComment::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                    ->where('user_id', token()->uid),
            ])
            ->groupBy('feeds.id')
            ->orderBy('feeds.id', 'desc')
            ->skip($page * $limit)->take($limit)->get();

        $feed_id = $data->pluck('feed_id');

        $missions = Mission::whereIn('feed_missions.feed_id', $feed_id)
            ->join('feed_missions', 'feed_missions.mission_id', 'missions.id')
            ->join('mission_categories', 'mission_categories.id', 'missions.mission_category_id')
            ->select([
                'feed_missions.feed_id', 'missions.id', 'missions.title', 'mission_categories.emoji',
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('user_id', $user_id)
                    ->whereColumn('mission_id', 'missions.id'),
            ])
            ->get();

        foreach ($missions->groupBy('feed_id') as $i => $mission) {
            $data[array_search($i, $feed_id->toArray())]->missions = $mission;
        }

        return success([
            'result' => true,
            'feeds' => $data,
        ]);
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
