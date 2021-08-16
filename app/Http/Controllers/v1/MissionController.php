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
use App\Models\MissionComment;
use App\Models\MissionImage;
use App\Models\MissionPlace;
use App\Models\MissionProduct;
use App\Models\MissionStat;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
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

            $product_id = $request->get('product_id');
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
                    'product_id' => $product_id
                ]);
            } elseif ($product_brand && $product_title && $product_price && $product_url) {
                MissionProduct::create([
                    'mission_id' => $data->id,
                    'type' => 'outside',
                    'image' => $product_image,
                    'brand' => $product_brand,
                    'title' => $product_title,
                    'price' => $product_price,
                    'url' => $product_url,
                ]);
            }

            if ($place_address && $place_title && $place_image) {
                MissionPlace::create([
                    'mission_id' => $data->id,
                    'address' => $place_address,
                    'title' => $place_title,
                    'description' => $place_description,
                    'image' => $place_image,
                    'url' => $place_url ?? urlencode("https://google.com/search?q=$place_title"),
                ]);
            }

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
    public function show($mission_id): array
    {
        DB::enableQueryLog();

        $user_id = token()->uid;

        $data = Mission::where('missions.id', $mission_id)
            ->join('users', 'users.id', 'missions.user_id') // 미션 제작자
            ->join('user_stats', 'user_stats.user_id', 'users.id') // 미션 제작자
            ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
            ->leftJoin('products', 'products.id', 'mission_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('mission_places', 'mission_places.mission_id', 'missions.id')
            ->select([
                'missions.id', 'missions.title', 'missions.description',
                'users.id as owner_id', 'users.nickname', 'users.profile_image', 'user_stats.gender', 'area' => area(),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('follows.target_id', 'users.id')
                    ->where('follows.user_id', $user_id),
                'mission_products.type as product_type', 'mission_products.product_id',
                DB::raw("IF(mission_products.type='inside', brands.name_ko, mission_products.brand) as product_brand"),
                DB::raw("IF(mission_products.type='inside', products.name_ko, mission_products.title) as product_title"),
                DB::raw("IF(mission_products.type='inside', products.thumbnail_image, mission_products.image) as product_image"),
                'mission_products.url as product_url',
                DB::raw("IF(mission_products.type='inside', products.price, mission_products.price) as product_price"),
                'mission_places.address as place_address', 'mission_places.title as place_title', 'mission_places.description as place_description',
                'mission_places.image as place_image', 'mission_places.url as place_url',
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                'bookmark_total' => MissionStat::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'),
                'comment_total' => MissionComment::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'),
            ])
            ->first();

        $data->owner = arr_group($data, ['owner_id', 'nickname', 'profile_image', 'gender', 'area', 'followers', 'is_following']);
        $data->product = arr_group($data, ['type', 'id', 'brand', 'title', 'image', 'url', 'price'], 'product_');
        $data->place = arr_group($data, ['address', 'title', 'description', 'image', 'url'], 'place_');

        $data->images = $data->images()->orderBy('order')->pluck('image');

        $data->users = $data->mission_stats()
            ->select(['users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender'])
            ->join('users', 'users.id', 'mission_stats.user_id')
            ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
            ->leftJoin('follows', 'follows.target_id', 'mission_stats.user_id')
            ->groupBy('users.id', 'user_stats.id')->orderBy(DB::raw('COUNT(follows.id)'), 'desc')->take(2)->get();

        $places = FeedPlace::whereExists(function ($query) use ($mission_id) {
            $query->selectRaw(1)->from('feed_missions')->whereColumn('feed_id', 'feeds.id')
                ->where('mission_id', $mission_id);
        })
            ->join('feeds', function ($query) use ($user_id) {
                $query->on('feeds.id', 'feed_places.feed_id')->whereNull('deleted_at')
                    ->where(function ($query) use ($user_id) {
                        $query->where('feeds.is_hidden', false)->orWhere('user_id', $user_id);
                    });
            })
            ->select(['feed_places.title', DB::raw("COUNT(distinct feeds.id) as feed_total")])
            ->groupBy('feed_places.title')
            ->orderBy('feed_total', 'desc')
            ->limit(3)
            ->get();

        if (count($places) > 0) {
            $query = null;
            function place_feed($user_id, $place, $mission_id)
            {
                return FeedPlace::where('feed_places.title', $place->title)
                    ->whereExists(function ($query) use ($mission_id) {
                        $query->selectRaw(1)->from('feed_missions')
                            ->whereColumn('feed_id', 'feeds.id')->where('mission_id', $mission_id);
                    })
                    ->where(function ($query) use ($user_id) {
                        $query->where('feeds.user_id', $user_id)->orWhere('feeds.is_hidden', false);
                    })
                    ->join('feeds', function ($query) {
                        $query->on('feeds.id', 'feed_places.feed_id')->whereNull('deleted_at');
                    })
                    ->join('users', 'users.id', 'feeds.user_id')
                    ->select([
                        'users.id as user_id', 'users.nickname', 'users.profile_image',
                        'feeds.id', 'feeds.created_at', 'feeds.content',
                        'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                        'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                        'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                        'check_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                        'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                        'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                            ->where('user_id', token()->uid),
                    ])
                    ->orderBy('id', 'desc')
                    ->take(10);
            }

            foreach ($places as $place) {
                if ($query) {
                    $query = place_feed($user_id, $place, $mission_id)->union($query);
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

        $products = FeedProduct::whereExists(function ($query) use ($mission_id) {
            $query->selectRaw(1)->from('feed_missions')->whereColumn('feed_id', 'feeds.id')
                ->where('mission_id', $mission_id);
        })
            ->join('feeds', function ($query) use ($user_id) {
                $query->on('feeds.id', 'feed_products.feed_id')->whereNull('deleted_at')
                    ->where(function ($query) use ($user_id) {
                        $query->where('feeds.is_hidden', false)->orWhere('user_id', $user_id);
                    });
            })
            ->select(['feed_products.title', DB::raw("COUNT(distinct feeds.id) as feed_total")])
            ->groupBy('feed_products.title')
            ->orderBy('feed_total', 'desc')
            ->limit(3)
            ->get();

        if (count($products) > 0) {
            $query = null;
            function product_feed($user_id, $product, $mission_id)
            {
                return FeedProduct::where('feed_products.title', $product->title)
                    ->whereExists(function ($query) use ($mission_id) {
                        $query->selectRaw(1)->from('feed_missions')
                            ->whereColumn('feed_id', 'feeds.id')->where('mission_id', $mission_id);
                    })
                    ->where(function ($query) use ($user_id) {
                        $query->where('feeds.user_id', $user_id)->orWhere('feeds.is_hidden', false);
                    })
                    ->join('feeds', function ($query) {
                        $query->on('feeds.id', 'feed_products.feed_id')->whereNull('deleted_at');
                    })
                    ->join('users', 'users.id', 'feeds.user_id')
                    ->select([
                        'users.id as user_id', 'users.nickname', 'users.profile_image',
                        'feeds.id', 'feeds.created_at', 'feeds.content',
                        'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                        'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                        'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                        'check_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                        'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                        'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                            ->where('user_id', token()->uid),
                    ])
                    ->orderBy('id', 'desc')
                    ->take(10);
            }

            foreach ($products as $product) {
                if ($query) {
                    $query = product_feed($user_id, $product, $mission_id)->union($query);
                } else {
                    $query = product_feed($user_id, $product, $mission_id);
                }
            }
            $query = $query->get();
            $keys = $products->pluck('title')->toArray();
            foreach ($query->groupBy('title') as $i => $item) {
                $products[array_search($i, $keys)]->feeds = $item;
            }
        }

        $feeds = FeedMission::where('feed_missions.mission_id', $mission_id)
            ->whereExists(function ($query) use ($mission_id) {
                $query->selectRaw(1)->from('feed_missions')
                    ->whereColumn('feed_id', 'feeds.id')->where('mission_id', $mission_id);
            })
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
                'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                'check_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                    ->where('user_id', token()->uid),
            ])
            ->orderBy('id', 'desc')
            ->get();

        return success([
            'result' => true,
            'mission' => $data,
            'places' => $places,
            'products' => $products,
            'feeds' => $feeds,
        ]);
    }

    public function user(Request $request, $mission_id): array
    {
        $user_id = token()->uid;

        $limit = $request->get('limit', 20);

        $users = MissionStat::where('mission_stats.mission_id', $mission_id)
            ->join('users', 'users.id', 'mission_stats.user_id')
            ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
            ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'mission_feeds' => FeedMission::selectRaw("COUNT(1)")
                    ->whereColumn('feed_missions.mission_id', 'mission_stats.mission_id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
            ])
            ->orderBy('mission_feeds')->orderBy('follower', 'desc')->orderBy('id', 'desc')
            ->take($limit)->get();

        return success([
            'success' => true,
            'users' => $users,
        ]);
    }
}
