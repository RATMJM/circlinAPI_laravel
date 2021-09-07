<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionComment;
use App\Models\MissionStat;
use App\Models\Place;
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

        $data = Place::when($category_id, function ($query, $category_id) {
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

        if (count($data)) {
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
                        'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                    ])
                    ->take(4);
            }

            $query = null;
            foreach ($data as $i => $item) {
                if ($query) {
                    $query = $query->union(missions($item->id, $category_id));
                } else {
                    $query = missions($item->id, $category_id);
                }
            }
            $query = $query->get();
            $keys = $data->pluck('id')->toArray();
            foreach ($query->groupBy('place_id') as $i => $item) {
                $data[array_search($i, $keys)]->missions = $item;
            }
        }

        return success([
            'result' => true,
            'places' => $data,
        ]);
    }

    public function show(Request $request, $id): array
    {
        $user_id = token()->uid;

        $page = $request->get('page', 0);
        $limit = $request->get('limit', 8);

        $data = Place::where('places.id', $id)
            ->select([
                'places.id', 'places.address', 'places.title', 'places.description',
                'places.image', 'places.url', 'places.lat', 'places.lng',
            ])
            ->withCount('missions')
            ->with('missions', function ($query) use ($page, $limit, $user_id) {
                $query->join('users', 'users.id', 'missions.user_id')
                    ->leftJoin('mission_areas', 'mission_areas.mission_id', 'missions.id')
                    ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
                    ->leftJoin('products', 'products.id', 'mission_products.product_id')
                    ->leftJoin('brands', 'brands.id', 'products.brand_id')
                    ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
                    ->select([
                        'mission_places.place_id',
                        'missions.id', 'missions.title', 'missions.description',
                        DB::raw("missions.event_order > 0 as is_event"),
                        DB::raw("missions.id <= 1213 and missions.event_order > 0 as is_old_event"), challenge_type(),
                        'missions.started_at', 'missions.ended_at',
                        'missions.thumbnail_image', 'missions.success_count',
                        'mission_area' => area_like('mission_areas'),
                        'bookmarks' => MissionStat::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id')
                            ->whereColumn('mission_stats.user_id', '!=', 'missions.user_id'),
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
                    ->orderBy('id', 'desc')->skip($page * $limit)->take($limit);
            })
            ->first();

        return success([
            'result' => true,
            'missions' => $data,
        ]);
    }
}
