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
use App\Models\User;
use App\Models\UserFavoriteCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MissionCategoryController extends Controller
{
    public function index($town = null): array
    {
        if ($town === 'town') {
            $user_id = token()->uid;

            $data = MissionCategory::whereNotNull('mission_category_id')
                ->where(function ($query) use ($user_id) {
                    // 관심카테고리
                    $query->whereHas('favorite_category', function ($query) use ($user_id) {
                        $query->where('user_id', $user_id);
                    });
                    // 북마크한 미션이 있는 카테고리
                    $query->orWhereHas('missions', function ($query) use ($user_id) {
                        $query->whereHas('mission_stats', function ($query) use ($user_id) {
                            $query->where('user_id', $user_id);
                        });
                    });
                })
                ->orWhere('id', 0)
                ->select([
                    'id', DB::raw("CAST(id as CHAR(20)) as `key`"), DB::raw("IFNULL(emoji, '') as emoji"),
                    'title',
                    'bookmark_total' => MissionStat::selectRaw("COUNT(1)")->where('mission_stats.user_id', $user_id)
                        ->whereHas('mission', function ($query) use ($user_id) {
                            $query->whereColumn('missions.mission_category_id', 'mission_categories.id');
                        }),
                    'is_favorite' => UserFavoriteCategory::selectRaw("COUNT(1) > 0")->where('user_id', $user_id)
                        ->whereColumn('user_favorite_categories.mission_category_id', 'mission_categories.id'),
                ])
                ->orderBy(DB::raw("id=0")) // 이벤트 탭 맨 뒤로
                ->orderBy(DB::raw("id=21")) // 기타 탭 맨 뒤로
                ->orderBy('bookmark_total', 'desc')->orderBy('is_favorite', 'desc')->orderBy('id')
                ->get();
        } else {
            $data = MissionCategory::whereNotNull('mission_category_id')
                ->select([
                    'mission_categories.id',
                    DB::raw("CAST(mission_categories.id as CHAR(20)) as `key`"),
                    DB::raw("IFNULL(mission_categories.emoji, '') as emoji"),
                    'mission_categories.title',
                    'mission_categories.description',
                ])->get();
        }

        return success([
            'result' => true,
            'categories' => $data,
        ]);
    }

    public function show(Request $request, $category_id): array
    {
        $user_id = token()->uid;

        if (!$category_id) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        $category = MissionCategory::where('id', $category_id)
            ->select([
                'mission_categories.id',
                DB::raw("IFNULL(mission_categories.emoji, '') as emoji"),
                'mission_categories.title',
                DB::raw("IFNULL(mission_categories.description, '') as description"),
            ])
            ->first();

        $users = UserFavoriteCategory::where('user_favorite_categories.mission_category_id', $category_id)
            ->join('users', 'users.id', 'user_favorite_categories.user_id')
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
            ])
            ->orderBy('follower', 'desc')->orderBy('id', 'desc');

        $user_total = $users->count();
        $users = $users->take(2)->get();

        $banners = (new BannerController())->category_banner($request, $category_id);
        $mission_total = Mission::where('mission_category_id', $category_id)->count();
        $missions = $this->mission($request, $category_id, 3)['data']['missions'];

        return success([
            'result' => true,
            'category' => $category,
            'user_total' => $user_total,
            'users' => $users,
            'banners' => $banners,
            'mission_total' => $mission_total,
            'missions' => $missions,
        ]);
    }

    public function mission(Request $request, $id = null, $limit = null, $page = null, $sort = null): array
    {
        $user_id = token()->uid;

        $limit = $limit ?? $request->get('limit', 20);
        $page = $page ?? $request->get('page', 0);
        $sort = $sort ?? $request->get('sort', 'recent');

        $local = $request->get('local');

        $missions = Mission::when($id, function ($query, $id) {
            $query->whereIn('missions.mission_category_id', Arr::wrap($id))
                ->where('event_order', 0);
        })
            ->when($id === 0, function ($query) {
                $query->where('event_order', '>', 0);
            })
            ->when($local, function ($query) use ($user_id) {
                $query//->whereNull('mission_areas.area_code')
                    ->where(User::select('area_code')->where('id', $user_id), 'like', DB::raw("CONCAT(mission_areas.area_code,'%')"));
            })
            ->leftJoin('mission_areas', 'mission_areas.mission_id', 'missions.id')
            ->select([
                'missions.id',
                'bookmarks' => FeedMission::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id')
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id')
                    ->whereColumn('user_id', '!=', 'missions.user_id'),
            ])
            ->orderBy(DB::raw("event_order=0"))
            ->orderBy('event_order');

        if ($sort === 'popular') {
            $missions->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
        } elseif ($sort === 'recent') {
            $missions->orderBy('missions.id', 'desc');
        } else {
            $missions->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
        }

        $missions_count = $missions->count();

        $missions = $missions->skip($page * $limit)->take($limit);

        $missions = Mission::joinSub($missions, 'm', function ($query) {
            $query->on('m.id', 'missions.id');
        })
            ->join('users', 'users.id', 'missions.user_id')
            ->leftJoin('mission_areas', 'mission_areas.mission_id', 'missions.id')
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
                'mission_area' => area_like('mission_areas'),
                'm.bookmarks',
                'comments' => MissionComment::selectRaw("COUNT(1)")->whereCOlumn('mission_id', 'missions.id'),
                'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'mission_stat_id' => MissionStat::withTrashed()->select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'mission_stat_user_id' => MissionStat::withTrashed()->select('user_id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('follows.user_id', $user_id),
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                'mission_products.type as product_type', //'mission_products.product_id', 'mission_products.outside_product_id',
                DB::raw("IF(mission_products.type='inside', mission_products.product_id, mission_products.outside_product_id) as product_brand"),
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
            ])
            ->withCount(['feeds' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }])
            ->get();

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

        if (count($missions)) {
            $query = null;
            foreach ($missions as $i => $item) {
                $missions[$i]->owner = arr_group($item, ['user_id', 'nickname', 'profile_image', 'gender',
                    'area', 'followers', 'is_following']);

                if ($query) {
                    $query = $query->union(mission_user($item->id));
                } else {
                    $query = mission_user($item->id);
                }
            }
            $query = $query->get();
            $keys = $missions->pluck('id')->toArray();
            foreach ($query->groupBy('mission_id') as $i => $item) {
                $missions[array_search($i, $keys)]->users = $item;
            }
        }

        return success([
            'result' => true,
            'missions_count' => $missions_count,
            'missions' => $missions,
        ]);
    }

    public function user(Request $request, $category_id): array
    {
        $user_id = token()->uid;

        $limit = $request->get('limit', 20);
        $page = $request->get('page', 0);

        $users = UserFavoriteCategory::where('user_favorite_categories.mission_category_id', $category_id)
            ->join('users', 'users.id', 'user_favorite_categories.user_id')
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
            ])
            ->orderBy('follower', 'desc')->orderBy('user_favorite_categories.id')
            ->skip($page * $limit)->take($limit)->get();

        return success([
            'success' => true,
            'users' => $users,
        ]);
    }
}
