<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionComment;
use App\Models\MissionStat;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PopularPlaceController extends Controller
{
    public function index(Request $request): array
    {
        $user_id = token()->uid;

        $category_id = $request->get('category_id');
        $page = $request->get('page', 0);
        $limit = $request->get('limit', 8);

        $missions = Place::when($category_id, function ($query, $category_id) {
            $query->where('missions.mission_category_id', $category_id);
        })
            ->join('mission_places', 'mission_places.mission_id', 'missions.id')
            ->join('missions', function ($query) {
                $query->on('missions.id', 'mission_places.mission_id')->whereNull('missions.deleted_at');
            })
            ->select([
                'places.id', 'places.address', 'places.title', 'places.description',
                'places.image', 'places.url', 'places.lat', 'places.lng',
                DB::raw("COUNT(distinct missions.id) as missions_count"),
            ])
            ->groupBy('places.id')
            ->orderBy('missions_count', 'desc')
            ->orderBy(DB::raw("MAX(missions.id)"), 'desc')
            ->skip($page * $limit)->take($limit)->get();

        if (count($missions)) {
            function missions($place_id, $category_id = null)
            {
                return Mission::where('mission_places.place_id', $place_id)
                    ->when($category_id, function ($query, $category_id) {
                        $query->where('missions.mission_category_id', $category_id);
                    })
                    ->join('mission_places', 'mission_places.mission_id', 'missions.id')
                    ->join('users', 'users.id', 'missions.user_id')
                    ->select([
                        'mission_places.place_id', 'missions.id', 'missions.title', 'missions.description', 'missions.thumbnail_image',
                        'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area_like(),
                    ])
                    ->take(4);
            }

            $query = null;
            foreach ($missions as $i => $item) {
                if ($query) {
                    $query = $query->union(missions($item->id, $category_id));
                } else {
                    $query = missions($item->id, $category_id);
                }
            }
            $query = $query->get();
            $keys = $missions->pluck('id')->toArray();
            foreach ($query->groupBy('place_id') as $i => $item) {
                $missions[array_search($i, $keys)]->missions = $item;
            }
        }

        return success([
            'result' => true,
            'places' => $missions,
        ]);
    }

    public function show(Request $request, $id): array
    {
        $user_id = token()->uid;

        $page = $request->get('page', 0);
        $limit = $request->get('limit', 8);
        $local = $request->get('local');

        $missions = Place::where('places.id', $id)
            ->withCount('missions')
            ->with('missions', function ($query) use ($page, $limit, $local, $user_id) {
                $query->when($local, function ($query) use ($user_id) {
                    $query->where(User::select('area_code')->where('id', $user_id), 'like', DB::raw("CONCAT(mission_areas.area_code,'%')"));
                })
                    ->leftJoin('mission_areas', 'mission_areas.mission_id', 'missions.id')
                    ->join('users', 'users.id', 'missions.user_id')
                    ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
                    ->leftJoin('products', 'products.id', 'mission_products.product_id')
                    ->leftJoin('brands', 'brands.id', 'products.brand_id')
                    ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
                    ->select([
                        'mission_places.place_id',
                        'missions.id', 'missions.title', 'missions.description',
                        'missions.is_event',
                        DB::raw("missions.id <= 1213 and missions.is_event = 1 as is_old_event"), challenge_type(),
                        'missions.started_at', 'missions.ended_at',
                        'missions.thumbnail_image', 'missions.success_count',
                        'bookmarks' => MissionStat::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id')
                            ->whereColumn('mission_stats.user_id', '!=', 'missions.user_id'),
                        'comments' => MissionComment::selectRaw("COUNT(1)")->whereCOlumn('mission_id', 'missions.id'),
                        'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area_like(),
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
                    ->groupBy('missions.id', 'mission_products.id')
                    ->orderBy('id', 'desc')->skip($page * $limit)->take($limit);
            })
            ->first();

        if (count($missions->missions)) {
            [$users, $areas] = null;
            foreach ($missions->missions as $i => $item) {
                $item->owner = arr_group($item, ['user_id', 'nickname', 'profile_image', 'gender',
                    'area', 'followers', 'is_following']);

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
            $keys = $missions->missions->pluck('id')->toArray();
            $users = $users->get();
            foreach ($users->groupBy('mission_id') as $i => $item) {
                $missions->missions[array_search($i, $keys)]->users = $item;
            }
            $areas = $areas->get();
            foreach ($areas->groupBy('mission_id') as $i => $item) {
                $missions->missions[array_search($i, $keys)]->areas = $item->pluck('name');
            }
        }

        return success([
            'result' => true,
            'missions' => $missions,
        ]);
    }
}
