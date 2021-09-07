<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\FeedComment;
use App\Models\FeedImage;
use App\Models\FeedLike;
use App\Models\FeedMission;
use App\Models\FeedPlace;
use App\Models\FeedProduct;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\MissionComment;
use App\Models\MissionImage;
use App\Models\MissionProduct;
use App\Models\MissionStat;
use App\Models\OutsideProduct;
use App\Models\Place;
use App\Models\User;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MissionController extends Controller
{
    public function store(Request $request): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;

            $mission_category_id = $request->get('mission_category_id');
            $title = $request->get('title');
            $description = $request->get('description');
            $thumbnail = $request->file('thumbnail');
            $files = $request->file('files');

            $success_count = $request->get('success_count', 0);

            $product_id = $request->get('product_id');
            $outside_product_id = $request->get('outside_product_id');
            $product_brand = $request->get('product_brand');
            $product_title = $request->get('product_title');
            $product_image = $request->get('product_image');
            $product_url = $request->get('product_url');
            $product_price = $request->get('product_price');

            $place_address = $request->get('place_address');
            $place_title = $request->get('place_title');
            $place_description = $request->get('place_description');
            $place_image = $request->get('place_image');
            $place_url = $request->get('place_url');
            $place_lat = $request->get('place_lat');
            $place_lng = $request->get('place_lng');

            if (!$title && !$files) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            $uploaded_thumbnail = null;
            if ($thumbnail) {
                if (str_starts_with($thumbnail->getMimeType(), 'image/')) {
                    $type = 'image';
                    $image = Image::make($thumbnail->getPathname());
                    if ($image->width() > $image->height()) {
                        $x = ($image->width() - $image->height()) / 2;
                        $y = 0;
                        $src = $image->height();
                    } else {
                        $x = 0;
                        $y = ($image->height() - $image->width()) / 2;
                        $src = $image->width();
                    }
                    $image->crop($src, $src, round($x), round($y));
                    $tmp_path = "{$thumbnail->getPath()}/{$user_id}_" . Str::uuid() . ".{$thumbnail->extension()}";
                    $image->save($tmp_path);
                    $uploaded_thumbnail = Storage::disk('ftp3')->put("/Image/USERPROMISE/$user_id", new File($tmp_path));
                    @unlink($tmp_path);
                }
            }

            $data = Mission::create([
                'user_id' => $user_id,
                'mission_category_id' => $mission_category_id,
                'title' => $title,
                'description' => $description,
                'thumbnail_image' => image_url(3, $uploaded_thumbnail),
                'success_count' => $success_count,
            ]);

            $user = User::find($user_id);

            $data->areas()->create([
                'mission_id' => $data->id,
                'area_code' => substr($user->area_code, 0, 5),
            ]);

            if ($files) {
                foreach ($files as $i => $file) {
                    $uploaded_thumbnail = '';
                    if (str_starts_with($file->getMimeType(), 'image/')) {
                        $type = 'image';
                        $image = Image::make($file->getPathname());
                        if ($image->width() > $image->height()) {
                            $x = ($image->width() - $image->height()) / 2;
                            $y = 0;
                            $src = $image->height();
                        } else {
                            $x = 0;
                            $y = ($image->height() - $image->width()) / 2;
                            $src = $image->width();
                        }
                        $image->crop($src, $src, round($x), round($y));
                        $tmp_path = "{$file->getPath()}/{$user_id}_" . Str::uuid() . ".{$file->extension()}";
                        $image->save($tmp_path);
                        $uploaded_file = Storage::disk('ftp3')->put("/Image/USERPROMISE/$user_id", new File($tmp_path));
                        @unlink($tmp_path);
                    } elseif (str_starts_with($file->getMimeType(), 'video/')) {
                        $type = 'video';
                        $uploaded_file = Storage::disk('ftp3')->put("/Image/USERPROMISE/$user_id", $file);

                        $thumbnail = "Image/SNS/$user_id/thumb_" . $file->hashName();

                        $host = config('filesystems.disks.ftp3.host');
                        $username = config('filesystems.disks.ftp3.username');
                        $password = config('filesystems.disks.ftp3.password');
                        $url = image_url(3, $uploaded_file);
                        $url2 = image_url(3, $thumbnail);
                        if (uploadVideoResizing($user_id, $host, $username, $password, $url, $url2, $data->id)) {
                            $uploaded_thumbnail = $thumbnail;
                        }
                    } else {
                        continue;
                    }

                    MissionImage::create([
                        'mission_id' => $data->id,
                        'order' => $i,
                        'type' => $type,
                        'image' => image_url(3, $uploaded_file),
                        'thumbnail_image' => image_url(3, $uploaded_thumbnail ?: $uploaded_file),
                    ]);
                }
            }

            if ($product_id) {
                MissionProduct::create([
                    'mission_id' => $data->id,
                    'type' => 'inside',
                    'product_id' => $product_id,
                ]);
            } elseif ($outside_product_id && $product_brand && $product_title && $product_price && $product_url) {
                $product = OutsideProduct::updateOrCreate(['product_id' => $outside_product_id], [
                    'image' => $product_image,
                    'brand' => $product_brand,
                    'title' => $product_title,
                    'price' => $product_price,
                    'url' => $product_url,
                ]);
                $data->product()->updateOrCreate([], ['type' => 'outside', 'outside_product_id' => $product->id]);
            }

            if ($place_address && $place_title) {
                $place = Place::updateOrCreate(['title' => $place_title], [
                    'address' => $place_address,
                    'description' => $place_description,
                    'image' => $place_image,
                    'url' => $place_url ?? urlencode("https://google.com/search?q=$place_title"),
                    'lat' => $place_lat,
                    'lng' => $place_lng,
                ]);
                $data->mission_place()->create(['place_id' => $place->id]);
            }

            (new BookmarkController())->store($request, $data->id);

            $this->invite($request, $data->id);

            DB::commit();

            return success(['result' => true, 'mission' => $data]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    /**
     * 미션 상세
     */
    public function show(Request $request, $mission_id): array
    {
        $user_id = token()->uid;
        $page = $request->get('page', 0);
        $limit = $request->get('limit', 10);

        $data = Mission::where('missions.id', $mission_id)
            ->join('users', 'users.id', 'missions.user_id') // 미션 제작자
            ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
            ->leftJoin('products', 'products.id', 'mission_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
            ->leftJoin('mission_places', 'mission_places.mission_id', 'missions.id')
            ->leftJoin('places', 'places.id', 'mission_places.place_id')
            ->select([
                'missions.id', 'category' => MissionCategory::select('title')->whereColumn('id', 'missions.mission_category_id'),
                'missions.title', 'missions.description',
                DB::raw("missions.event_order > 0 as is_event"),
                DB::raw("missions.id <= 1213 and missions.event_order > 0 as is_old_event"), challenge_type(),
                'missions.thumbnail_image', 'missions.success_count',
                'mission_stat_id' => MissionStat::select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->limit(1),
                'users.id as owner_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('follows.target_id', 'users.id')
                    ->where('follows.user_id', $user_id),
                'mission_products.type as product_type', //'mission_products.product_id',
                DB::raw("IF(mission_products.type='inside', mission_products.product_id, mission_products.outside_product_id) as product_id"),
                DB::raw("IF(mission_products.type='inside', brands.name_ko, outside_products.brand) as product_brand"),
                DB::raw("IF(mission_products.type='inside', products.name_ko, outside_products.title) as product_title"),
                DB::raw("IF(mission_products.type='inside', products.thumbnail_image, outside_products.image) as product_image"),
                'outside_products.url as product_url',
                DB::raw("IF(mission_products.type='inside', products.price, outside_products.price) as product_price"),
                'places.address as place_address', 'places.title as place_title', 'places.description as place_description',
                'places.image as place_image', 'places.url as place_url',
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                'bookmark_total' => MissionStat::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id')
                    ->whereColumn('mission_stats.user_id', '!=', 'missions.user_id'),
                'comment_total' => MissionComment::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'),
            ])
            ->withCount(['feeds' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }])
            ->first();

        if (is_null($data)) {
            return success([
                'result' => false,
                'reason' => 'not found mission',
            ]);
        }

        $data->owner = arr_group($data, ['owner_id', 'nickname', 'profile_image', 'gender', 'area', 'followers', 'is_following']);
        $data->product = arr_group($data, ['type', 'id', 'brand', 'title', 'image', 'url', 'price'], 'product_');
        $data->place = arr_group($data, ['address', 'title', 'description', 'image', 'url'], 'place_');

        $data->images = $data->images()->orderBy('order')->pluck('image');

        $data->users = FeedMission::where('feed_missions.mission_id', $mission_id)
            ->where(Mission::select('user_id')->whereColumn('id', 'feed_missions.mission_id')->limit(1), '!=', DB::raw('feeds.user_id'))
            ->join('feeds', 'feeds.id', 'feed_missions.feed_id')
            ->join('users', 'users.id', 'feeds.user_id')
            ->select(['mission_id', 'users.id', 'users.nickname', 'users.profile_image', 'users.gender'])
            ->groupBy('users.id', 'mission_id')
            ->orderBy(DB::raw("COUNT(distinct feeds.id)"), 'desc')
            ->take(2)
            ->get();

        /*$places = FeedMission::where('mission_id', $mission_id)
            ->join('feeds', function ($query) use ($user_id) {
                $query->on('feeds.id', 'feed_missions.feed_id')
                    ->where(function ($query) use ($user_id) {
                        $query->where('feeds.is_hidden', false)->orWhere('user_id', $user_id);
                    });
            })
            ->join('places', 'places.id', 'feeds.place_id')
            ->select([
                'places.id', 'places.title', 'places.address', 'places.description',
                'places.image', 'places.url',
                DB::raw("COUNT(distinct feeds.id) as feed_total"),
            ])
            ->groupBy('places.id')
            ->orderBy('feed_total', 'desc')
            ->limit(3)
            ->get();

        if (count($places) > 0) {
            function place_feed($user_id, $place, $mission_id)
            {
                return Place::where('places.id', $place->id)
                    ->whereExists(function ($query) use ($mission_id) {
                        $query->selectRaw(1)->from('feed_missions')
                            ->whereColumn('feed_id', 'feeds.id')->where('mission_id', $mission_id);
                    })
                    ->whereNull('feeds.deleted_at')
                    ->join('feeds', function ($query) use ($user_id) {
                        $query->on('feeds.place_id', 'places.id')->whereNull('deleted_at')
                            ->where(function ($query) use ($user_id) {
                                $query->where('feeds.user_id', $user_id)->orWhere('feeds.is_hidden', false);
                            });
                    })
                    ->join('users', 'users.id', 'feeds.user_id')
                    ->select([
                        'users.id as user_id', 'users.nickname', 'users.profile_image',
                        'feeds.id', 'feeds.created_at', 'feeds.content',
                        'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                        'has_product' => FeedProduct::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), // 상품 있는지
                        'has_place' => Place::selectRaw("COUNT(1) > 0")->whereColumn('id', 'feeds.place_id'), // 위치 있는지
                        'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                        'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                        'check_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                        'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                        'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                            ->where('user_id', token()->uid),
                        'has_comment' => FeedComment::selectRaw("COUNT(1) > 0")->whereColumn('feed_comments.feed_id', 'feeds.id')
                            ->where('feed_comments.user_id', token()->uid),
                    ])
                    ->orderBy('id', 'desc')
                    ->take(10);
            }

            $query = null;
            foreach ($places as $place) {
                if ($query) {
                    $query = $query->union(place_feed($user_id, $place, $mission_id));
                } else {
                    $query = place_feed($user_id, $place, $mission_id);
                }
            }
            $query = $query->get();
            $keys = $places->pluck('title')->toArray();
            foreach ($query->groupBy('title') as $i => $item) {
                $places[array_search($i, $keys)]->feeds = $item;
            }
        }

        $products = FeedMission::where('mission_id', $mission_id)
            ->join('feeds', function ($query) use ($user_id) {
                $query->on('feeds.id', 'feed_missions.feed_id')
                    ->where(function ($query) use ($user_id) {
                        $query->where('feeds.is_hidden', false)->orWhere('user_id', $user_id);
                    });
            })
            ->join('feed_products', 'feed_products.feed_id', 'feeds.id')
            ->leftJoin('products', 'products.id', 'feed_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'feed_products.outside_product_id')
            ->select([
                'feed_products.type as product_type', 'feed_products.product_id', 'feed_products.outside_product_id',
                DB::raw("IF(feed_products.type='inside', brands.name_ko, outside_products.brand) as product_brand"),
                DB::raw("IF(feed_products.type='inside', products.name_ko, outside_products.title) as product_title"),
                DB::raw("IF(feed_products.type='inside', products.thumbnail_image, outside_products.image) as product_image"),
                'outside_products.url as product_url',
                DB::raw("IF(feed_products.type='inside', products.price, outside_products.price) as product_price"),
                DB::raw("COUNT(distinct feeds.id) as feed_total"),
            ])
            ->groupBy('feed_products.type', 'feed_products.product_id', 'feed_products.outside_product_id')
            ->orderBy('feed_total', 'desc')
            ->limit(3)
            ->get();

        if (count($products) > 0) {
            function product_feed($user_id, $product, $mission_id)
            {
                return match ($product->product_type) {
                    'inside' => Product::where('products.id', $product->product_id)
                        ->whereExists(function ($query) use ($mission_id) {
                            $query->selectRaw(1)->from('feed_missions')
                                ->whereColumn('feed_id', 'feeds.id')->where('mission_id', $mission_id);
                        })
                        ->where(function ($query) use ($user_id) {
                            $query->where('feeds.user_id', $user_id)->orWhere('feeds.is_hidden', false);
                        })
                        ->join('feed_products', 'feed_products.product_id', 'products.id')
                        ->join('feeds', function ($query) {
                            $query->on('feeds.id', 'feed_products.feed_id')->whereNull('deleted_at');
                        })
                        ->join('users', 'users.id', 'feeds.user_id')
                        ->select([
                            'users.id as user_id', 'users.nickname', 'users.profile_image',
                            'feeds.id', 'feeds.created_at', 'feeds.content',
                            'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                            'has_product' => FeedProduct::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), // 상품 있는지
                            'has_place' => Place::selectRaw("COUNT(1) > 0")->whereColumn('id', 'feeds.place_id'), // 위치 있는지
                            'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                            'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                            'check_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                            'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                            'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                                ->where('user_id', token()->uid),
                            'has_comment' => FeedComment::selectRaw("COUNT(1) > 0")->whereColumn('feed_comments.feed_id', 'feeds.id')
                                ->where('feed_comments.user_id', token()->uid),
                        ])
                        ->orderBy('id', 'desc')
                        ->take(10),
                    'outside' => OutsideProduct::where('outside_products.id', $product->outside_product_id)
                        ->whereExists(function ($query) use ($mission_id) {
                            $query->selectRaw(1)->from('feed_missions')
                                ->whereColumn('feed_id', 'feeds.id')->where('mission_id', $mission_id);
                        })
                        ->where(function ($query) use ($user_id) {
                            $query->where('feeds.user_id', $user_id)->orWhere('feeds.is_hidden', false);
                        })
                        ->join('feed_products', 'feed_products.outside_product_id', 'outside_products.id')
                        ->join('feeds', function ($query) {
                            $query->on('feeds.id', 'feed_products.feed_id')->whereNull('deleted_at');
                        })
                        ->join('users', 'users.id', 'feeds.user_id')
                        ->select([
                            'users.id as user_id', 'users.nickname', 'users.profile_image',
                            'feeds.id', 'feeds.created_at', 'feeds.content',
                            'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                            'has_product' => FeedProduct::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), // 상품 있는지
                            'has_place' => Place::selectRaw("COUNT(1) > 0")->whereColumn('id', 'feeds.place_id'), // 위치 있는지
                            'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                            'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                            'check_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                            'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                            'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                                ->where('user_id', token()->uid),
                            'has_comment' => FeedComment::selectRaw("COUNT(1) > 0")->whereColumn('feed_comments.feed_id', 'feeds.id')
                                ->where('feed_comments.user_id', token()->uid),
                        ])
                        ->orderBy('id', 'desc')
                        ->take(10),
                    default => null
                };
            }

            $query = null;
            foreach ($products as $product) {
                if ($query) {
                    $query = $query->union(product_feed($user_id, $product, $mission_id));
                } else {
                    $query = product_feed($user_id, $product, $mission_id);
                }
            }
            $query = $query->get();
            $keys = $products->pluck('title')->toArray();
            foreach ($query->groupBy('title') as $i => $item) {
                $products[array_search($i, $keys)]->feeds = $item;
            }
        }*/

        $feeds = $this->feed($request, $mission_id)['data'];

        return success([
            'result' => true,
            'mission' => $data,
            // 'places' => $places,
            // 'products' => $products,
            'feeds_count' => $feeds['feeds_count'],
            'feeds' => $feeds['feeds'],
        ]);
    }

    public function feed(Request $request, $mission_id): array
    {
        $user_id = token()->uid;
        $page = $request->get('page', 0);
        $limit = $request->get('limit', 10);

        $feeds = FeedMission::where('feed_missions.mission_id', $mission_id)
            ->whereNull('feeds.deleted_at')
            ->where(function ($query) use ($user_id) {
                $query->where('feeds.user_id', $user_id)->orWhere('feeds.is_hidden', false);
            })
            ->join('feeds', function ($query) {
                $query->on('feeds.id', 'feed_missions.feed_id')->whereNull('deleted_at');
            })
            ->join('users', 'users.id', 'feeds.user_id')
            ->select([
                'users.id as user_id', 'users.nickname', 'users.profile_image',
                'feeds.id', 'feeds.created_at', 'feeds.content',
                'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                'has_product' => FeedProduct::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), // 상품 있는지
                'has_place' => FeedPlace::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), // 위치 있는지
                'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                'check_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                    ->where('user_id', token()->uid),
                'has_comment' => FeedComment::selectRaw("COUNT(1) > 0")->whereColumn('feed_comments.feed_id', 'feeds.id')
                    ->where('feed_comments.user_id', token()->uid),
            ])
            ->orderBy('id', 'desc');
        $feeds_count = $feeds->count();
        $feeds = $feeds->skip($page * $limit)->take($limit)->get();

        return success([
            'result' => true,
            'feeds_count' => $feeds_count,
            'feeds' => $feeds,
        ]);
    }

    public function edit($mission_id): array
    {
        $user_id = token()->uid;

        $mission = Mission::where('id', $mission_id)
            ->select(['id', 'user_id', 'title', 'description', 'thumbnail_image'])
            ->with('place', function ($query) {
                $query->select(['mission_id', 'title', 'address', 'description', 'image', 'url']);
            })
            ->with('product', function ($query) {
                $query->select(['mission_id', 'type', 'product_id', 'brand', 'title', 'image', 'url', 'price']);
            })
            ->first();

        if (is_null($mission)) {
            return success([
                'result' => false,
                'reason' => 'not found mission',
            ]);
        }

        if ($mission->user_id != $user_id) {
            return success([
                'result' => false,
                'reason' => 'access denied',
            ]);
        }

        return success([
            'result' => true,
            'mission' => $mission,
        ]);
    }

    public function update(Request $request, $mission_id): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;

            if (count($request->all()) === 0) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            $mission = Mission::where('id', $mission_id)->first();

            if (is_null($mission)) {
                return success([
                    'result' => false,
                    'reason' => 'not found mission',
                ]);
            }

            if ($mission->user_id != $user_id) {
                return success([
                    'result' => false,
                    'reason' => 'access denied',
                ]);
            }

            $description = trim($request->get('description'));

            $product_delete = $request->get('product_delete');
            $product_id = $request->get('product_id');
            $outside_product_id = $request->get('outside_product_id');
            $product_brand = $request->get('product_brand');
            $product_title = $request->get('product_title');
            $product_image = $request->get('product_image');
            $product_url = $request->get('product_url');
            $product_price = $request->get('product_price');

            $place_delete = $request->get('place_delete');
            $place_address = $request->get('place_address');
            $place_title = $request->get('place_title');
            $place_description = $request->get('place_description');
            $place_image = $request->get('place_image');
            $place_url = $request->get('place_url');
            $place_lat = $request->get('place_lat');
            $place_lng = $request->get('place_lng');

            if (isset($description) && $description !== '') {
                $mission->update(['description' => $description]);
            }

            if ($product_delete) {
                $mission->product()->delete();
            } elseif ($product_id) {
                $mission->product()->updateOrCreate([], [
                    'type' => 'inside',
                    'product_id' => $product_id,
                ]);
            } elseif ($outside_product_id && $product_brand && $product_title && $product_price && $product_url) {
                $product = OutsideProduct::updateOrCreate(['product_id' => $outside_product_id], [
                    'image' => $product_image,
                    'brand' => $product_brand,
                    'title' => $product_title,
                    'price' => $product_price,
                    'url' => $product_url,
                ]);
                $mission->product()->updateOrCreate([], ['type' => 'outside', 'outside_product_id' => $product->id]);
            }


            if ($place_delete) {
                $mission->mission_place()->delete();
            } elseif ($place_address && $place_title) {
                $place = Place::updateOrCreate(['title' => $place_title], [
                    'address' => $place_address,
                    'description' => $place_description,
                    'image' => $place_image,
                    'url' => $place_url ?? urlencode("https://google.com/search?q=$place_title"),
                    'lat' => $place_lat,
                    'lng' => $place_lng,
                ]);
                $mission->mission_place()->updateOrCreate([], ['place_id' => $place->id]);
            }

            DB::commit();
            return success(['result' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function user(Request $request, $mission_id): array
    {
        $user_id = token()->uid;

        $limit = $request->get('limit', 20);
        $page = $request->get('page', 0);

        $users = MissionStat::where('mission_stats.mission_id', $mission_id)
            ->join('users', 'users.id', 'mission_stats.user_id')
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'mission_feeds' => FeedMission::selectRaw("COUNT(1)")
                    ->whereColumn('mission_id', 'mission_stats.mission_id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
            ])
            ->orderBy('mission_feeds')->orderBy('follower', 'desc')->orderBy('id', 'desc')
            ->skip($page * $limit)->take($limit)->get();

        return success([
            'success' => true,
            'users' => $users,
        ]);
    }

    public function invite(Request $request, $mission_id): array
    {
        try {
            DB::beginTransaction();

            $users = Arr::wrap($request->get('user_id', $request->get('invite_id')));
            $users = array_unique($users);
            $success = [];
            $sockets = [];
            foreach ($users as $user) {
                $res = (new ChatController())->send_direct($request, $user, 'mission', $mission_id,
                    '미션에 초대합니다!');
                if ($res['success'] && $res['data']['result']) {
                    $success[] = $user;
                    $sockets = Arr::collapse([$sockets, $res['data']['sockets']]);
                }
            }

            DB::commit();

            return success(['result' => true, 'users' => $success, 'sockets' => $sockets]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function destroy($id): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;

            $mission = Mission::where('id', $id)->first();

            if ($mission->user_id === $user_id) {
                $mission->mission_stats()->delete();
                $data = $mission->delete();

                DB::commit();
                return success(['result' => true]);
            } else {
                DB::rollBack();
                return success([
                    'result' => false,
                    'reason' => 'not my mission',
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    //이벤트챌린지 룸(챌린지 신청한 상태) 데이터 조회
    public function event_mission_info(Request $request): array
    {
        $user_id = token()->uid;
        $mission_stat_id = $request->get('challPk');
        $mission_id = $request->get('challId');
        $time = date("Y-m-d H:i:s");
        $today = date("Y-m-d");
        $yesterDay = date('Y-m-d', $_SERVER['REQUEST_TIME'] - 86400);
        // $user_id =1;//token()->uid;
        // $mission_stat_id = "1042518";//  $request->get('challPk');
        // $mission_id = "962" ;//$request->get('challId');
        // $time = date("Y-m-d H:i:s");
        // $today = date("Y-m-d");
        // $yesterDay = date('Y-m-d', $_SERVER['REQUEST_TIME']-86400);


        try {
            DB::beginTransaction();
            $event_mission_info = DB::select('SELECT  d.id as mission_stat_id, d.image as certification_image,
             b.id as mission_id , 
             CASE WHEN ? ="1213" THEN "40000" ELSE "" END AS MAX_NUM, gender, nickname, profile_image, a.id as user_id,  
             ifnull(c.RANK,0) as RANK, 
             round(d.goal_distance - e.distance,3) as REMAIN_DIST, goal_distance , 
             e.distance, e.laptime, e.laptime_origin, e.distance_origin,
               (select count(user_id) from mission_stats where mission_id=1213 and user_id=a.id) as SCORE ,
              case when d.completed_at is null then "" else "1" end as BONUS_FLAG,   
              CASE when date_add(SYSDATE() , interval + 9 hour ) between b.reserve_started_at and b.reserve_ended_at then "R"
              when date_add(SYSDATE() , interval + 9 hour ) between b.started_at and b.ended_at then "Y"
              ELSE "N" end as STATE,
 
               ifnull((select count(user_id) from follows where target_id= ? ) ,0) as FOLLOWER, 
               ifnull(( select count(user_id) from mission_stats where mission_id= ? ),0) as CHALL_PARTI, 
               b.started_at as START_DATE, Adddate(b.ended_at, interval 1 day )  as END_DAY1,
               (SELECT COUNT(*) FROM  feed_missions WHERE  mission_stat_id = ? 
               and mission_id= ? and substr(created_at,1,10)= ?)  as CERT_TODAY,
               (SELECT count(k.mission_id) FROM mission_stats k 
                 WHERE k.mission_id=? and completed_at is not null) as FINISH,
              ifnull(   ( SELECT ifnull(YEST.RANK-TODAY.RANK,"0") CHANGED FROM
                             (SELECT b.RANK, b.USER_PK, a.TIER, a.NICKNAME, a.PROFILE_IMG, a.FOLLOWER
                             FROM circlinDEV.MEMBERDATA a, circlinDEV.RUN_RANK b
                             WHERE  a._ID=b.USER_PK
                             and USER_PK= ?  and INS_DATE=? and DEL_YN="N" and b.SEX="A" and b.CHALL_ID= ? limit 0,1) TODAY 
                             LEFT JOIN 
                             (SELECT b.RANK, b.USER_PK, a.TIER, a.NICKNAME, a.PROFILE_IMG, a.FOLLOWER
                             FROM circlinDEV.MEMBERDATA a, circlinDEV.RUN_RANK b
                             WHERE
                             a._ID=b.USER_PK and USER_PK= ?  and INS_DATE= ? and DEL_YN="N" 
                             and b.SEX="A" and b.CHALL_ID= ? limit 0,1)  YEST  on  TODAY.USER_PK=YEST.USER_PK
                        ),"") as CHANGED,
                     g.bg_image,
                     g.info_image_1 , 
                     g.info_image_2, 
                     g.info_image_3,
                     g.info_image_4,
                     g.info_image_5,
                     g.info_image_6,
                     g.info_image_7,
                     g.intro_image_1, 
                     g.intro_image_2, 
                     g.intro_image_3,
                     g.intro_image_4,
                     g.intro_image_5,
                     g.intro_image_6,
                     g.intro_image_7, g.intro_image_8, g.intro_image_9, g.intro_image_10,
                     g.subtitle_1 , `description`, g.subtitle_3 , g.subtitle_4 ,g.subtitle_5 , g.subtitle_6, g.subtitle_7 
             FROM users a, 
             missions b LEFT JOIN circlinDEV.CHALLENGE_INFO_2 f on b.id=f.CHALLINFO_PK
                        LEFT JOIN mission_etc g on  b.id=g.mission_id , 
             mission_stats d 
             LEFT JOIN circlinDEV.RUN_RANK c on  d.id = c.CHALL_PK and c.SEX="A" and c.DEL_YN="N" and c.INS_DATE= ? 
             left join feed_missions e on   d.id=e.mission_stat_id
             
             where b.id=d.mission_id
             and d.user_id=a.id
             -- and b.id=e.mission_id
             -- and e.mission_stat_id=d.id 
             and a.id= ?    and d.id=? and b.id =?
              ; ', [$mission_id,
                $user_id,
                $mission_id,
                $mission_stat_id, $mission_id, $today, $mission_id,
                $user_id, $today, $mission_id, $user_id, $yesterDay, $mission_id,
                $today,
                $user_id, $mission_stat_id, $mission_id]);


        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }


        try {
            DB::beginTransaction();
            $recent_user = DB::select('select a.user_id, b.nickname, b.profile_image, 
             (SELECT count(target_id) FROM follows WHERE  user_id=a.user_id ) as follower,
             ifnull((SELECT "Y" FROM follows WHERE user_id= ? and target_id=a.user_id LIMIT 0,1),"N") as follow_yn
             From mission_stats a, users b 
             where a.mission_id= ? and a.completed_at is null 
             and b.id=a.user_id and b.deleted_at is null
             order by a.created_at desc limit 15
              ; ', [$user_id, $mission_id,]);


        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

        // 참가자 총 거리
        try {
            DB::beginTransaction();
            $total_km = DB::select('select ifnull(sum(distance),0) as total_km From feed_missions a where mission_id= ? ; ',
                [$mission_id,]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

        // 내 기록
        try {
            DB::beginTransaction();
            $myRecord = DB::select('Select a.user_id, a.content, a.created_at, b.feed_id, 
             (select image from feed_images x where a.id=x.feed_id and `order`=0 ) as image,
             (select type from feed_images x where a.id=x.feed_id and `order`=0 ) as type, 
             b.distance, b.laptime, c.goal_distance,  
             e.title as place_title , e.address as place_address, e.image as place_image, e.url as place_url
             from feeds a left join feed_places f on a.id=f.feed_id , places e, feed_missions b, mission_stats c, missions d
             where b.feed_id=a.id and c.mission_id=d.id and b.mission_stat_id=c.id  and b.mission_id=d.id
             and a.user_id=c.user_id and a.deleted_at is null and f.place_id = e.id
             and a.user_id= ? 
             and b.mission_id= ? 
             and b.mission_stat_id = ?; ',
                [$user_id, $mission_id, $mission_stat_id]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
        // 내 미션 상태
        try {
            DB::beginTransaction();
            $mission_stat = DB::select('select count(b.id) as day_count, ifnull(round(avg(b.distance),2),0) as distance,
         ifnull(sum(b.distance),0) total_distance, 
           ifnull(ROUND((sum(b.distance) / c.goal_distance) * 100 ,0),0) as progress,
            sum( CASE WHEN cast(c.goal_distance as unsigned ) <= cast(b.distance as unsigned) then  1 else 0 end ) as success_today,
            ifnull((select count(id) from feed_missions where mission_id= ? ) ,0) cert_count            
           from feeds a, feed_missions b , mission_stats c
         where a.id=b.feed_id and b.mission_id=c.mission_id and b.mission_stat_id=c.id
         and a.user_id= ?
         and b.mission_id= ?
         and b.mission_stat_id= ?
         GROUP BY  b.distance, c.goal_distance',
                [$mission_id, $user_id, $mission_id, $mission_stat_id]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

        // $state = $data[0]->STATE;
        // $sex = $data[0]->SEX;
        // $challId = $data[0]->CHALL_ID;
        // $score = $data[0]->SCORE;
        // $bonusFlag = $data[0]->BONUS_FLAG;
        // $rank = $data[0]->RANK;
        // $certToday = $data[0]->CERT_TODAY;
        // $finCnt = $data[0]->FINISH;

        return success([
            'success' => true,
            'event_mission_info' => $event_mission_info,
            'recent_user' => $recent_user,
            'total_km' => $total_km,
            'myRecord' => $myRecord,
            'mission_stat' => $mission_stat,

        ]);
    }


    //이벤트챌린지 소개페이지(신청페이지) 데이터 조회
    public function mission_info(Request $request): array
    {
        $user_id = token()->uid;
        $mission_id = $request->get('mission_id');
        // $user_id         = 4;//token()->uid;
        // $mission_id      = 786;//$request->get('mission_id');
        $time = date("Y-m-d H:i:s");

        try {
            DB::beginTransaction();
            //미션 댓글 입력
            $mission_info = DB::select('SELECT youtube, youtube_text, a.week_duration, a.week_min_count, a.thumbnail_image,  a.reward_point, a.title as mission_name, a.user_limit,
            a.id as mission_id, a.user_id, c.logo, c.apply_image1, c.apply_image2, c.apply_image3, c.apply_image4, c.apply_image5,
            c.apply_image6, 
            subtitle_1 , `description`,subtitle_3 , subtitle_4 ,subtitle_5 , subtitle_6 ,subtitle_7 ,
            desc1, desc2, bg_image, video_1,
            ifnull(( select count(user_id) from mission_stats where mission_id= ? ),0) as participants, 
            ifnull(( select count(user_id) from mission_likes where mission_id= ? ),0) as likes,
             
             intro_image_1,intro_image_2,intro_image_3,intro_image_4,intro_image_5,intro_image_6,intro_image_7,intro_image_8,intro_image_9,intro_image_10,
             owner.nickname as owner_nickname, owner.profile_image , 
             ifnull((select count(user_id) from follows where a.user_id= target_id ) ,0) as FOLLOWER, 
             a.reserve_started_at, a.reserve_ended_at, a.started_at, a.ended_at, 
             "https://www.circlin.co.kr/SNS/assets/img/marathon.mp4" as IMG_URL1,
             "https://www.circlin.co.kr/SNS/assets/img/maraTab2.png" as IMG_URL2,
             "https://www.circlin.co.kr/SNS/assets/img/maraTab3.png" as IMG_URL3,
             "https://www.circlin.co.kr/SNS/assets/img/maraTab4.png" as IMG_URL4,
             "https://www.circlin.co.kr/SNS/assets/img/maraTab5.png" as IMG_URL5,
             "https://www.circlin.co.kr/SNS/assets/img/maraTab6.png" as IMG_URL6,
             "https://www.circlin.co.kr/SNS/assets/img/medal_design.png" as IMG_MEDAL,
        ifnull((SELECT "Y" FROM mission_likes n WHERE user_id= ? and a.id=n.mission_id),"N" )as like_yn ,         
        ifnull((SELECT id FROM mission_stats WHERE user_id= ? and ended_at is null and completed_at is null and mission_id= ? ),"" ) as mission_stat_id,
            CASE when date_add(SYSDATE() , interval + 9 hour ) between a.reserve_started_at and a.reserve_ended_at then "PRE"
                            when date_add(SYSDATE() , interval + 9 hour ) between a.started_at and a.ended_at then "START"
                            ELSE "END" end as CHECK_START
                            , d.name_ko as product_name
                            , d.id as product_id
                            , d.thumbnail_image as product_image
                             
            from   missions a 
					LEFT JOIN mission_etc c on  a.id=c.mission_id 
					LEFT JOIN mission_products b on b.mission_id=a.id
                    LEFT JOIN products d on b.product_id = d.id,  `users` as owner 
            where a.user_id=owner.id and a.id=? and a.deleted_at is null;'
                , [$mission_id,
                    $mission_id,
                    $user_id,
                    $user_id,
                    $mission_id,
                    $mission_id]);

            if ($mission_info[0]->mission_stat_id == null) {
                $do_yn = 'N';
            } else {
                $do_yn = 'Y';
            }
            return success([
                'success' => true,
                'mission' => $mission_info,
                'do_yn' => $do_yn,
                'mission_stat_id' => $mission_info[0]->mission_stat_id,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

    }


    public function start_event_mission(Request $request): array
    {
        $user_id = token()->uid;
        $mission_id = $request->get('mission_id');
        // $user_id         = 4;//token()->uid;
        // $mission_id      = 786;//$request->get('mission_id');
        $time = date("Y-m-d H:i:s");
        // insertExtUserCode($uid,$code,$challId);


        //미션마스터 조회
        $mission_info = DB::select('select started_at, ended_at, CASE when date_add(SYSDATE() , interval + 9 hour ) between a.reserve_started_at and a.reserve_ended_at then "PRE"
            when date_add(SYSDATE() , interval + 9 hour ) between a.started_at and a.ended_at then "START"
            ELSE "" end as START_DATE_CHECK, 
            (select   count(id) +1 from mission_stats where mission_id = ?) as user_count
            From missions a
            where id= ?;'
            , [$mission_id, $mission_id]);

        if ($mission_info[0]->START_DATE_CHECK == 'PRE') { // 오늘날짜와 비교해서 당일이면 상태를 시작상태(Y)로 변경
            $state = 'R'; // 안쓰는 로직으로 변경됨. 무조건 다음날 부터 선택가능
            $timestamp1 = $mission_info[0]->started_at;
            $today = "";
        } elseif ($mission_info[0]->START_DATE_CHECK == 'START') {
            $state = 'Y';
            // $timestamp1 = $checkDate;
            $today = date("Y-m-d H:i:s");
        } elseif ($mission_info[0]->START_DATE_CHECK == 'END') {
            return success([
                'success' => 'END',
            ]);
        }


        try {
            DB::beginTransaction();
            //미션   입력
            $start_mission = DB::insert('insert into mission_stats(created_at, updated_at, user_id, mission_id)
                                            values( ?, ? ,? ,?) ;'
                , [$time, $time,
                    $user_id,
                    $mission_id,
                ]);

            DB::commit();


            $mission_stat_id = DB::select('select max(id) as mission_stat_id
            From mission_stats a
            where mission_id= ? and user_id= ?  ;'
                , [$mission_id, $user_id]);

            return success([
                'success' => true,
                'mission_stat_id' => $mission_stat_id,
                'user_count' => $mission_info[0]->user_count,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

    }

    //참가자 조회
    public function participant_list(Request $request): array
    {
        $user_id = token()->uid;
        $mission_id = $request->get('mission_id');
        $time = date("Y-m-d H:i:s");


        try {
            DB::beginTransaction();
            //참가자 조회
            $participant_list = DB::select('select a.user_id, b.nickname, b.profile_image, 
            (SELECT count(target_id) FROM follows WHERE  user_id=a.user_id ) as follower,
            ifnull((SELECT "Y" FROM follows WHERE user_id= ? and target_id=a.user_id LIMIT 0,1),"N") as follow_yn
            From mission_stats a, users b 
            where a.mission_id= ? and a.completed_at is null 
            and b.id=a.user_id and b.deleted_at is null
            order by a.created_at desc;'
                , [$user_id,
                    $mission_id,]);

            return success([
                'success' => true,
                'participant_list' => $participant_list,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

    }

    public function certification_image(Request $request): array
    {
        $user_id = token()->uid;
        $mission_stat_id = $request->get('mission_stat_id');
        $data = User::where('id', $user_id)->first();

        if (is_null($data) || !$request->file('file')) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

// echo '??';
        $file = $request->file('file');
        if (str_starts_with($file->getMimeType() ?? '', 'image/')) {
            // 정사각형으로 자르기
            $image = Image::make($file->getPathname());
            if ($image->width() > $image->height()) {
                $x = ($image->width() - $image->height()) / 2;
                $y = 0;
                $src = $image->height();
            } else {
                $x = 0;
                $y = ($image->height() - $image->width()) / 2;
                $src = $image->width();
            }
            $image->crop($src, $src, round($x), round($y));
            $tmp_path = "{$file->getPath()}/{$user_id}_" . Str::uuid() . ".{$file->extension()}";
            $image->save($tmp_path);

            if ($filename = Storage::disk('ftp2')->put("/Image/profile/$user_id", new File($tmp_path))) { //파일전송 성공
                $filename = image_url(2, $filename);
                try {
                    DB::beginTransaction();
                    //인증서 사진 업로드
                    $certification_image = DB::update('UPDATE mission_stats set image = ? where id = ? ;'
                        , [$filename,
                            $mission_stat_id]);

                    DB::commit();

                    return success([
                        'success' => true,
                        'certification_image' => $certification_image,
                        'filename' => $filename,
                        'mission_stat_id' => $mission_stat_id,
                    ]);

                } catch (Exception $e) {
                    DB::rollBack();
                    return exceped($e);
                }

            } else {
                return success(['result' => false, 'reason' => 'upload failed']);
            }
        } else {
            return success(['result' => false, 'reason' => 'not image']);
        }
    }

    //더블존탭
    public function doublezone_feed_list(Request $request): array
    {
        $user_id = token()->uid;
        $type = $request->get('type');
        $mission_id = $request->get('mission_id');
        $place_id = $request->get('place_id');
        $page = $request->get('page');
        $time = date("Y-m-d H:i:s");
        $today = date("Y-m-d");
        $yesterDay = date('Y-m-d', $_SERVER['REQUEST_TIME'] - 86400);
        // $user_id =1;//token()->uid;
        // $mission_stat_id = "1042518";//  $request->get('challPk');
        // $mission_id = "962" ;//$request->get('challId');
        // $place_id = $request->get('place_id');
        // $time = date("Y-m-d H:i:s");
        // $today = date("Y-m-d");
        // $yesterDay = date('Y-m-d', $_SERVER['REQUEST_TIME']-86400);
    

        if($type=='ALL'){
            try {
                DB::beginTransaction();
                $double_zone_feed = DB::select('Select a.user_id, a.content, a.created_at, b.feed_id, 
                (select image from feed_images x where a.id=x.feed_id and `order`=0 ) as image,
                (select type from feed_images x where a.id=x.feed_id and `order`=0 ) as type, 
                b.distance, b.laptime, c.goal_distance,  
                e.title as place_title , e.address as place_address, e.image as place_image, e.url as place_url, f.place_id,
                (select count(1) from feed_likes where feed_id=a.id ) as check_total,
                (select count(1) from feed_comments where feed_id=a.id ) as comment_total,
                (select count(1)>0 from feed_likes where feed_id=a.id and user_id= ? ) as has_check   ,
                (select count(1)>0 from feed_comments where feed_id=a.id and user_id= ? ) as has_comment  ,
                case when f.place_id is null then null else "1" end as has_place  ,
                (select count(1)>0 from feed_products where feed_id=a.id   ) as has_product  
                from feeds a left join feed_places f on a.id=f.feed_id, places e, feed_missions b, mission_stats c, missions d
                where b.feed_id=a.id and c.mission_id=d.id and b.mission_stat_id=c.id  and b.mission_id=d.id
                and a.user_id=c.user_id and a.deleted_at is null and f.place_id = e.id
                and b.mission_id= ?   
                order by feed_id desc limit ?, 20;',
                    [ $user_id, $user_id,$mission_id, $page]);
            } catch (Exception $e) {
                DB::rollBack();
                return exceped($e);
            }

        }else if($type=='ETC'){
            try {
                DB::beginTransaction();
                $double_zone_feed = DB::select('Select a.user_id, a.content, a.created_at, b.feed_id, 
                (select image from feed_images x where a.id=x.feed_id and `order`=0 ) as image,
                (select type from feed_images x where a.id=x.feed_id and `order`=0 ) as type, 
                b.distance, b.laptime, c.goal_distance,  
                e.title as place_title , e.address as place_address, e.image as place_image, e.url as place_url, f.place_id,
                (select count(1) from feed_likes where feed_id=a.id ) as check_total,
                (select count(1) from feed_comments where feed_id=a.id ) as comment_total,
                (select count(1)>0 from feed_likes where feed_id=a.id and user_id= ? ) as has_check   ,
                (select count(1)>0 from feed_comments where feed_id=a.id and user_id= ? ) as has_comment  ,
                case when f.place_id is null then null else "1" end as has_place  ,
                (select count(1)>0 from feed_products where feed_id=a.id   ) as has_product  
                from feeds a left join feed_places f on a.id=f.feed_id, places e, feed_missions b, mission_stats c, missions d
                where b.feed_id=a.id and c.mission_id=d.id and b.mission_stat_id=c.id  and b.mission_id=d.id
                and a.user_id=c.user_id and a.deleted_at is null and f.place_id = e.id
                and b.mission_id= ?  and f.place_id= ?
                order by feed_id desc limit ?, 20;',
                    [ $user_id, $user_id,$mission_id, $place_id, $page]);
            } catch (Exception $e) {
                DB::rollBack();
                return exceped($e);
            }

        }
        else{
            try {
                DB::beginTransaction();
                $double_zone_feed = DB::select('Select a.user_id, a.content, a.created_at, b.feed_id, 
                (select image from feed_images x where a.id=x.feed_id and `order`=0 ) as image,
                (select type from feed_images x where a.id=x.feed_id and `order`=0 ) as type, 
                b.distance, b.laptime, c.goal_distance,  
                e.title as place_title , e.address as place_address, e.image as place_image, e.url as place_url, f.place_id,
                (select count(1) from feed_likes where feed_id=a.id ) as check_total,
                (select count(1) from feed_comments where feed_id=a.id ) as comment_total,
                (select count(1)>0 from feed_likes where feed_id=a.id and user_id= ? ) as has_check   ,
                (select count(1)>0 from feed_comments where feed_id=a.id and user_id= ? ) as has_comment  ,
                case when f.place_id is null then null else "1" end as has_place  ,
                (select count(1)>0 from feed_products where feed_id=a.id   ) as has_product  
                from feeds a left join feed_places f on a.id=f.feed_id, places e, feed_missions b, mission_stats c, missions d
                where b.feed_id=a.id and c.mission_id=d.id and b.mission_stat_id=c.id  and b.mission_id=d.id
                and a.user_id=c.user_id and a.deleted_at is null and f.place_id = e.id
                and b.mission_id= ?  and f.place_id= ?
                order by feed_id desc limit ?, 20;',
                    [ $user_id, $user_id,$mission_id, $place_id, $page]);
            } catch (Exception $e) {
                DB::rollBack();
                return exceped($e);
            }

        }

         
        return success([
            'success' => true,
            'double_zone_feed' => $double_zone_feed,
             

        ]);
    }
}
