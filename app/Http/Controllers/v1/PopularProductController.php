<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\MissionProduct;
use App\Models\OutsideProduct;
use App\Models\Product;
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

        $data = MissionProduct::when($category_id, function ($query, $category_id) {
            $query->where('missions.mission_category_id', $category_id);
        })
            ->leftJoin('products', 'products.id', 'mission_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
            ->join('missions', function ($query) {
                $query->on('missions.id', 'mission_products.mission_id')->whereNull('missions.deleted_at');
            })
            ->select([
                'mission_products.type', //'mission_products.product_id', 'mission_products.outside_product_id',
                DB::raw("IF(mission_products.type='inside', mission_products.product_id, mission_products.outside_product_id) as product_id"),
                DB::raw("IF(mission_products.type='inside', brands.name_ko, outside_products.brand) as brand"),
                DB::raw("IF(mission_products.type='inside', products.name_ko, outside_products.title) as title"),
                DB::raw("IF(mission_products.type='inside', products.thumbnail_image, outside_products.image) as image"),
                'outside_products.url as url',
                DB::raw("IF(mission_products.type='inside', products.price, outside_products.price) as price"),
                DB::raw("COUNT(distinct missions.id) as missions_count"),
            ])
            ->groupBy('type', 'product_id', 'outside_product_id')
            ->orderBy('missions_count', 'desc')
            ->orderBy(DB::raw("MAX(missions.id)"), 'desc')
            ->skip($page * $limit)->take($limit)->get();

        if (count($data)) {
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
                    ->take(4);
            }

            $query = null;
            foreach ($data as $i => $item) {
                $id = $item->type === 'inside' ? 'product_id' : 'outside_product_id';
                if ($query) {
                    $query = $query->union(missions($id, $item->product_id, $category_id));
                } else {
                    $query = missions($id, $item->product_id, $category_id);
                }
            }
            $query = $query->get();
            $keys = $data->pluck('product_id')->toArray();
            foreach ($query->groupBy('product_id') as $i => $item) {
                $data[array_search($i, $keys)]->missions = $item;
            }
        }

        return success([
            'result' => true,
            'places' => $data,
        ]);
    }

    public function show(Request $request, $type, $id): array
    {
        $user_id = token()->uid;

        $page = $request->get('page', 0);
        $limit = $request->get('limit', 8);

        if ($type === 'inside') {
            $data = Product::where('products.id', $id)
                ->leftJoin('brands', 'brands.id', 'products.brand_id')
                ->select([
                    'products.id', 'brands.name_ko as brand', 'products.name_ko as title',
                    'products.thumbnail_image as image', DB::raw("null as url"), 'products.price',
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
        } else {
            $data = OutsideProduct::where('outside_products.id', $id)
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

        return success([
            'result' => true,
            'missions' => $data,
        ]);
    }
}
