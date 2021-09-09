<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\MissionComment;
use App\Models\MissionProduct;
use App\Models\MissionStat;
use App\Models\OutsideProduct;
use App\Models\Place;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PopularProductController extends Controller
{
    public function index(Request $request): array
    {
        $user_id = token()->uid;

        $category_id = $request->get('category_id');
        $page = $request->get('page', 0);
        $limit = $request->get('limit', 8);

        $missions = MissionProduct::when($category_id, function ($query, $category_id) {
            $query->where('missions.mission_category_id', $category_id);
        })
            ->where(function ($query) {
                $query->where('mission_products.type', 'inside')
                    ->whereNotNull('products.id')
                    ->orWhere('mission_products.type', 'outside');
            })
            ->leftJoin('products', function ($query) {
                $query->on('products.id', 'mission_products.product_id')
                    ->where('is_skin', false)->whereNull('deleted_at');
            })
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
            ->join('missions', function ($query) {
                $query->on('missions.id', 'mission_products.mission_id')->whereNull('missions.deleted_at');
            })
            ->select([
                'mission_products.type',
                DB::raw("IF(mission_products.type='inside', products.id, outside_products.id) as product_id"),
                DB::raw("IF(mission_products.type='inside', brands.name_ko, outside_products.brand) as brand"),
                DB::raw("IF(mission_products.type='inside', products.name_ko, outside_products.title) as title"),
                DB::raw("IF(mission_products.type='inside', products.thumbnail_image, outside_products.image) as image"),
                'outside_products.url as url',
                DB::raw("IF(mission_products.type='inside', products.price, outside_products.price) as price"),
                DB::raw("COUNT(distinct missions.id) as missions_count"),
            ])
            ->groupBy('type', 'products.id', 'outside_products.id')
            ->orderBy('missions_count', 'desc')
            ->orderBy(DB::raw("MAX(missions.id)"), 'desc')
            ->skip($page * $limit)->take($limit)->get();

        if (count($missions)) {
            function missions($type, $product_id, $category_id = null)
            {
                return MissionProduct::where(function ($query) use ($type, $product_id) {
                    $query->where($type, $product_id);
                })
                    ->when($category_id, function ($query, $category_id) {
                        $query->where('missions.mission_category_id', $category_id);
                    })
                    ->join('missions', 'missions.id', 'mission_products.mission_id')
                    ->join('users', 'users.id', 'missions.user_id')
                    ->select([
                        DB::raw("IF(mission_products.type='inside', mission_products.product_id, mission_products.outside_product_id) as product_id"),
                        'missions.id', 'missions.title', 'missions.description', 'missions.thumbnail_image',
                        'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                    ])
                    ->orderBy('missions.id', 'desc')
                    ->take(4);
            }

            $query = null;
            foreach ($missions as $i => $item) {
                $id = $item->type === 'inside' ? 'product_id' : 'outside_product_id';
                if ($query) {
                    $query = $query->union(missions($id, $item->product_id, $category_id));
                } else {
                    $query = missions($id, $item->product_id, $category_id);
                }
            }
            $query = $query->get();
            $keys = $missions->pluck('product_id')->toArray();
            foreach ($query->groupBy('product_id') as $i => $item) {
                $missions[array_search($i, $keys)]->missions = $item;
            }
        }

        return success([
            'result' => true,
            'products' => $missions,
        ]);
    }

    public function show(Request $request, $type, $id): array
    {
        $user_id = token()->uid;

        $page = $request->get('page', 0);
        $limit = $request->get('limit', 8);
        $local = $request->get('local');

        if ($type === 'inside') {
            $missions = Product::where('products.id', $id)
                ->leftJoin('brands', 'brands.id', 'products.brand_id')
                ->select([
                    'products.id', 'brands.name_ko as brand', 'products.name_ko as title',
                    'products.thumbnail_image as image', DB::raw("null as url"), 'products.price',
                ])
                ->withCount('missions')
                ->with('missions', function ($query) use ($page, $limit, $local, $user_id) {
                    $query->when($local, function ($query) use ($user_id) {
                        $query->where(User::select('area_code')->where('id', $user_id), 'like', DB::raw("CONCAT(mission_areas.area_code,'%')"));
                    })
                        ->join('users', 'users.id', 'missions.user_id')
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
                        ->orderBy('missions.id', 'desc')->skip($page * $limit)->take($limit);
                })
                ->first();
        } else {
            $missions = OutsideProduct::where('outside_products.id', $id)
                ->select([
                    'id', 'brand', 'title', 'image', 'url', 'price',
                ])
                ->withCount('missions')
                ->with('missions', function ($query) use ($page, $limit) {
                    $query->join('users', 'users.id', 'missions.user_id')
                        ->select([
                            'missions.id', 'missions.title', 'missions.description', 'missions.thumbnail_image',
                            'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                        ])
                        ->orderBy('missions.id', 'desc')->skip($page * $limit)->take($limit);
                })
                ->first();
        }

        if (count($missions->missions)) {
            [$users, $areas] = null;
            foreach ($missions->missions as $i => $item) {
                $item->owner = arr_group($item, ['user_id', 'nickname', 'profile_image', 'gender',
                    'area', 'followers', 'is_following']);
                // $item->areas = mission_areas($item->id)->pluck('name');

                if ($users) {
                    $users = $users->union(mission_users($item->id));
                } else {
                    $users = mission_users($item->id);
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
