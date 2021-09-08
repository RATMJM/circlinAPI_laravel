<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\FeedMission;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\MissionComment;
use App\Models\MissionStat;
use App\Models\Place;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request): array
    {
        $user_id = token()->uid;

        $users = (new BaseController())->suggest_user($request)['data']['users'];

        $categories = MissionCategory::whereNotNull('mission_category_id')
            ->select([
                'id', DB::raw("IFNULL(emoji, '') as emoji"), 'title',
                DB::raw("IFNULL(description, '') as description")
            ])
            ->orderBy('id')->get();

        return success([
            'result' => true,
            'users' => $users,
            'categories' => $categories,
        ]);
    }

    public function search(Request $request): array
    {
        $user_id = token()->uid;
        $keyword = $request->get('keyword');

        if ($keyword) {
            $users = $this->user($request)['data']['users'];
            $missions = $this->mission($request)['data']['missions'];

            SearchHistory::create([
                'user_id' => $user_id,
                'keyword' => $keyword,
            ]);

            return success([
                'result' => true,
                'users' => $users,
                'missions' => $missions,
            ]);
        } else {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }
    }

    public function simple(Request $request): array
    {
        $user_id = token()->uid;
        $keyword = $request->get('keyword');
        $keyword2 = str_replace([' ', '%'], '', $keyword);

        if ($keyword) {
            // users
            $data = User::where(DB::raw("REPLACE(nickname, ' ', '')"), 'like', "%$keyword2%")
                ->select([DB::raw("nickname COLLATE utf8mb4_unicode_ci as keyword"), DB::raw("'user' as type")]);
            // missions
            $data = $data->union(Mission::where(DB::raw("REPLACE(title, ' ', '')"), 'like', "%$keyword2%")
                ->select(['title as keyword', DB::raw("'mission' as type")]));
            // search_histories
            $data = $data->union(SearchHistory::where(DB::raw("REPLACE(keyword, ' ', '')"), 'like', "%$keyword2%")
                ->select(['keyword', DB::raw("'keyword' as type")]))
                ->orderBy(DB::raw("LENGTH(keyword)"));
            $data = DB::table($data)
                ->select([
                    'keyword', DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(`type` separator '|'), '|', 1) as `type`")
                ])
                ->groupBy('keyword')
                ->orderBy(DB::raw("LENGTH(keyword)"))
                ->take(10)->get();

            return success([
                'result' => true,
                'data' => $data,
            ]);
        } else {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }
    }

    public function user(Request $request): array
    {
        $user_id = token()->uid;
        $page = $request->get('page', 0);
        $limit = $request->get('limit', 20);
        $keyword = $request->get('keyword');
        $keyword2 = str_replace([' ', '%'], '', $keyword);

        $data = User::where(DB::raw("REPLACE(users.nickname,' ','')"), 'like', "%$keyword2%")
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
                'together_following' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id')
                    ->whereHas('user_target_follow', function ($query) use ($user_id) {
                        $query->where('user_id', $user_id);
                    }),
            ])
            ->orderBy('together_following', 'desc')->orderby('is_following', 'desc')->orderBy('follower', 'desc')
            ->skip($page * $limit)->take($limit)
            ->get();

        return success([
            'success' => true,
            'users' => $data,
        ]);
    }

    public function mission(Request $request): array
    {
        $user_id = token()->uid;
        $page = $request->get('page', 0);
        $limit = $request->get('limit', 20);
        $keyword = $request->get('keyword');
        $keyword2 = str_replace([' ', '%'], '', $keyword);

        $data = Mission::where(DB::raw("REPLACE(missions.title,' ','')"), 'like', "%$keyword2%")
            ->join('users', 'users.id', 'missions.user_id') // 미션 제작자
            ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
            ->leftJoin('products', 'products.id', 'mission_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
            ->select([
                'missions.id', 'missions.title', 'missions.description',
                DB::raw("missions.event_order > 0 as is_event"),
                DB::raw("missions.id <= 1213 and missions.event_order > 0 as is_old_event"), challenge_type(),
                'missions.started_at', 'missions.ended_at',
                'missions.thumbnail_image', 'missions.success_count',
                'mission_stat_id' => MissionStat::withTrashed()->select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'mission_stat_user_id' => MissionStat::withTrashed()->select('user_id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'users.id as owner_id', 'users.nickname as owner_nickname',
                'users.profile_image as owner_profile_image', 'users.gender as owner_gender',
                'owner_area' => area(),
                'owner_followers' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'owner_is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('follows.target_id', 'users.id')
                    ->where('follows.user_id', $user_id),
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                'mission_products.type as product_type', 'mission_products.product_id', 'mission_products.outside_product_id',
                DB::raw("IF(mission_products.type='inside', brands.name_ko, outside_products.brand) as product_brand"),
                DB::raw("IF(mission_products.type='inside', products.name_ko, outside_products.title) as product_title"),
                DB::raw("IF(mission_products.type='inside', products.thumbnail_image, outside_products.image) as product_image"),
                'outside_products.url as product_url',
                DB::raw("IF(mission_products.type='inside', products.price, outside_products.price) as product_price"),
                'place_address' => Place::select('address')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_title' => Place::select('title')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_description' => Place::select('description')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_image' => Place::select('image')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_url' => Place::select('url')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'bookmarks' => FeedMission::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id')
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id')
                    ->whereColumn('user_id', '!=', 'missions.user_id'),
                'comments' => MissionComment::selectRaw("COUNT(1)")->whereCOlumn('mission_id', 'missions.id'),
            ])
            ->withCount(['feeds' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }])
            ->orderBy('is_bookmark', 'desc')->orderBy('bookmarks', 'desc')->orderBy('id', 'desc')
            ->skip($page * $limit)->take($limit)
            ->get();

        foreach ($data as $i => $item) {
            $data[$i]->owner = arr_group($data[$i],
                ['id', 'nickname', 'profile_image', 'gender', 'area', 'followers', 'is_following'], 'owner_');
        }

        if (count($data)) {
            function mission_user($mission_id)
            {
                return FeedMission::where('feed_missions.mission_id', $mission_id)
                    ->where(Mission::select('user_id')->whereColumn('id', 'feed_missions.mission_id')->limit(1), '!=', DB::raw('feeds.user_id'))
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id')
                    ->join('users', 'users.id', 'feeds.user_id')
                    ->select(['mission_id', 'users.id', 'users.nickname', 'users.profile_image', 'users.gender'])
                    ->groupBy('users.id', 'mission_id')
                    ->orderBy(DB::raw("COUNT(distinct feeds.id)"), 'desc')
                    ->take(2);
            }

            $users = null;
            foreach ($data as $i => $mission) {
                if ($users) {
                    $users = $users->union(mission_user($mission->id));
                } else {
                    $users = mission_user($mission->id);
                }
            }
            $users = $users->get();
            $keys = $data->pluck('id')->toArray();
            foreach ($users->groupBy('mission_id') as $j => $item) {
                $data[array_search($j, $keys)]->users = $item;
            }
        }

        return success([
            'success' => true,
            'missions' => $data,
        ]);
    }
}
