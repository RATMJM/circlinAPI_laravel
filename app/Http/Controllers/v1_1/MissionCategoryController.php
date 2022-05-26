<?php

namespace App\Http\Controllers\v1_1;

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
        $user_id = token_option()?->uid;
        if ($town === 'town') {
            $data = MissionCategory::where(function ($query) use ($user_id) {
                $query->whereIn('mission_categories.id',
                    UserFavoriteCategory::select('mission_category_id')->where('user_id', $user_id)
                )
                    ->orWhereIn('mission_categories.id',
                        MissionStat::select('mission_category_id')
                            ->join('missions', 'missions.id', 'mission_id')
                            ->where('mission_stats.user_id', $user_id)
                    )
                    ->orWhere('mission_categories.id', 0);
            });
        } else {
            $data = MissionCategory::query();
        }

        $data = $data->select([
            'mission_categories.id',
            DB::raw("CAST(mission_categories.id as CHAR(20)) as `key`"),
            DB::raw("IFNULL(mission_categories.emoji, '') as emoji"),
            'mission_categories.title',
            'bookmark_total' => MissionStat::selectRaw("COUNT(distinct id)")
                ->where(Mission::select('mission_category_id')
                    ->whereColumn('missions.id', 'mission_id'), DB::raw('mission_categories.id'))
                ->where('user_id', $user_id),
            'is_favorite' => UserFavoriteCategory::selectRaw("COUNT(1) > 0")
                ->whereColumn('mission_category_id', 'mission_categories.id')
                ->where('user_id', $user_id),
        ])
            ->whereNotNull('mission_categories.mission_category_id')
            ->groupBy('mission_categories.id')
            ->orderBy(DB::raw("mission_categories.id=0"), 'desc') // 이벤트 탭 맨 앞으로
            ->orderBy(DB::raw("mission_categories.id=21")) // 기타 탭 맨 뒤으로
            ->orderBy('bookmark_total')
            ->orderBy('is_favorite')
            ->orderBy('mission_categories.id', 'desc')
            ->get();

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

        $category = MissionCategory::where('mission_categories.id', $category_id)
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
                'users.id',
                'users.nickname',
                'users.profile_image',
                'users.gender',
                'area' => area_like(),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
            ])
            ->orderBy('follower', 'desc')->orderBy('user_favorite_categories.mission_category_id', 'desc');

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
        $sort = $sort ?? $request->get('sort', SORT_RECENT);

        $local = $request->get('local');

        $missions = Mission::where('is_show', true)
            ->when($id, function ($query, $id) {
                $query->whereIn('missions.mission_category_id', Arr::wrap($id))
                    ->where('is_event', 0);
            })
            ->when($id == 0, function ($query) {
                $query->where('is_event', 1);
                /*->orderBy(DB::raw("#(missions.started_at is null or missions.started_at<=now()) and
                    (missions.ended_at is null or missions.ended_at>now())"), 'desc')*/
            })
            ->when($local, function ($query) use ($user_id) {
                $query->where(User::select('area_code')
                    ->where('id', $user_id), 'like', DB::raw("CONCAT(mission_areas.area_code,'%')"));
            })
            ->leftJoin('mission_areas', 'mission_areas.mission_id', 'missions.id')
            ->select('missions.id')
            ->groupBy('missions.id');

        $missions_count = DB::table($missions)->count();

        $missions->select([
            'missions.id',
            'bookmarks' => MissionStat::withTrashed()->selectRaw("COUNT(distinct user_id)")
                ->whereColumn('mission_id', 'missions.id'),
        ]);

        if ($sort == SORT_POPULAR) {
            $missions->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
        } elseif ($sort == SORT_RECENT) {
            $missions->orderBy('event_order', 'desc')->orderBy('id', 'desc');
        } elseif ($sort == SORT_USER) {
            $missions->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
        } elseif ($sort == SORT_COMMENT) {
            $missions->orderBy(MissionComment::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'), 'desc');
        }

        $missions = $missions->skip($page * $limit)->take($limit);

        $missions = Mission::joinSub($missions, 'm', function ($query) {
            $query->on('m.id', 'missions.id');
        })
            ->join('users', 'users.id', 'missions.user_id')
            ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
            ->leftJoin('products', 'products.id', 'mission_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
            ->select([
                'missions.id',
                'missions.title',
                'missions.description',
                'missions.is_event',
                DB::raw("missions.id <= 1213 and missions.is_event = 1 as is_old_event"),
                'missions.event_type',
                'missions.is_ground',
                'missions.is_ocr',
                'missions.started_at',
                'missions.ended_at',
                is_available(),
                DB::raw("CASE WHEN
                    (missions.started_at is null or missions.started_at <= now()) and
                    (missions.ended_at is null or missions.ended_at >= now())
                THEN 'ongoing'
                WHEN (missions.reserve_started_at is null or missions.reserve_started_at <= now()) and
                    (missions.reserve_ended_at is null or missions.reserve_ended_at >= now())
                THEN 'reserve'
                WHEN missions.reserve_started_at >= now() THEN 'before' ELSE 'end' END as `status`"),
                'missions.thumbnail_image',
                'missions.success_count',
                'm.bookmarks',
                'comments' => MissionComment::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'),
                'users.id as user_id',
                'users.nickname',
                'users.profile_image',
                'users.gender',
                'area' => area_like(),
                'mission_stat_id' => MissionStat::withTrashed()->select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'mission_stat_user_id' => MissionStat::withTrashed()
                    ->select('user_id')
                    ->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)
                    ->orderBy('id', 'desc')
                    ->limit(1),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('follows.user_id', $user_id),
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                'mission_products.type as product_type',
                //'mission_products.product_id', 'mission_products.outside_product_id',
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
                'place_description' => Place::select('description')
                    ->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')
                    ->limit(1),
                'place_image' => Place::select('image')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_url' => Place::select('url')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'feeds_count' => FeedMission::selectRaw("COUNT(distinct feeds.id)")
                    ->whereColumn('mission_id', 'missions.id')
                    ->join('feeds', function ($query) use ($user_id) {
                        $query->on('feeds.id', 'feed_missions.feed_id')
                            ->whereNull('feeds.deleted_at')
                            ->where(function ($query) use ($user_id) {
                                // $query->where('feeds.is_hidden', 0)->orWhere('feeds.user_id', $user_id);
                            });
                    })
                    ->where('user_id', $user_id),
            ])
            ->get();

        if (count($missions)) {
            [$users, $areas] = null;
            foreach ($missions as $i => $item) {
                $item->owner = arr_group($item, [
                    'user_id',
                    'nickname',
                    'profile_image',
                    'gender',
                    'area',
                    'followers',
                    'is_following',
                ]);

                if ($users) {
                    $users = $users->union(mission_users($item->id, $user_id));
                } else {
                    $users = mission_users($item->id, $user_id);
                }

                if ($areas) {
                    $areas = $areas->union(mission_areas($item->id));
                } else {
                    $areas = mission_areas($item->id);
                }
            }
            $keys = $missions->pluck('id')->toArray();
            $users = $users->get();
            foreach ($users->groupBy('mission_id') as $i => $item) {
                $missions[array_search($i, $keys)]->users = $item;
            }
            $areas = $areas->get();
            foreach ($areas->groupBy('mission_id') as $i => $item) {
                $missions[array_search($i, $keys)]->areas = $item->pluck('name');
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
                'users.id',
                'users.nickname',
                'users.profile_image',
                'users.gender',
                'area' => area_like(),
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
