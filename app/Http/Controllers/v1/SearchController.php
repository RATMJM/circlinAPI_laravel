<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\MissionComment;
use App\Models\MissionStat;
use App\Models\SearchHistory;
use App\Models\User;
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
                'id', DB::raw("COALESCE(emoji, '') as emoji"), 'title',
                DB::raw("COALESCE(description, '') as description")
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
        $keyword2 = str_replace(' ', '', $keyword);

        if ($keyword) {
            // users
            $data = User::where(DB::raw("REPLACE(nickname, ' ', '')"), 'like', "$keyword2%")
                ->select(['nickname as keyword', DB::raw("'user' as type")]);
            // missions
            $data = $data->union(Mission::where(DB::raw("REPLACE(title, ' ', '')"), 'like', "$keyword2%")
                ->select(['title', DB::raw("'mission'")]));
            // search_histories
            $data = $data->union(SearchHistory::where(DB::raw("REPLACE(keyword, ' ', '')"), 'like', "$keyword2%")
                ->select(['keyword', DB::raw("'keyword'")])
                ->groupBy('keyword'))
                ->orderBy(DB::raw("LENGTH(keyword)"))
                ->take(10)
                ->get();

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

        $data = User::where('users.nickname', 'like', "%$keyword%")
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

        $data = Mission::where('missions.title', 'like', "%$keyword%")
            ->join('users', 'users.id', 'missions.user_id') // 미션 제작자
            ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
            ->leftJoin('products', 'products.id', 'mission_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
            ->leftJoin('places', 'places.id', 'missions.place_id')
            ->select([
                'missions.id', 'missions.title', 'missions.description',
                DB::raw("missions.event_order > 0 as is_event"), 'missions.thumbnail_image',
                'missions.success_count',
                'mission_stat_id' => MissionStat::select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->limit(1),
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
                'places.address as place_address', 'places.title as place_title', 'places.description as place_description',
                'places.image as place_image', 'places.url as place_url',
                'bookmarks' => MissionStat::selectRaw("COUNT(1)")->whereCOlumn('mission_id', 'missions.id'),
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

        return success([
            'success' => true,
            'missions' => $data,
        ]);
    }
}
