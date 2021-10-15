<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Feed;
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
use App\Models\MissionGround;
use App\Models\MissionGroundText;
use App\Models\MissionImage;
use App\Models\MissionPlace;
use App\Models\MissionProduct;
use App\Models\MissionPush;
use App\Models\MissionStat;
use App\Models\OutsideProduct;
use App\Models\Place;
use App\Models\User;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

            $content_code = $request->get('content_code');
            $content_title = $request->get('content_title');
            $content_description = $request->get('content_description');
            $content_channel = $request->get('content_channel_title');
            $content_thumbnail_image = $request->get('content_thumbnail_image');

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

                    $image->orientate();

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

            $data->mission_areas()->create([
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

                        $image->orientate();

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
            } elseif ($outside_product_id && $product_title && $product_price && $product_url) {
                $product = OutsideProduct::updateOrCreate(['url' => $product_url], [
                    'product_id' => $outside_product_id,
                    'image' => $product_image,
                    'brand' => $product_brand,
                    'title' => $product_title,
                    'price' => $product_price,
                ]);
                $data->product()->updateOrCreate([], ['type' => 'outside', 'outside_product_id' => $product->id]);
            }

            if ($place_address && $place_title) {
                $place = Place::updateOrCreate(['title' => $place_title, 'address' => $place_address], [
                    'description' => $place_description,
                    'image' => $place_image,
                    'url' => $place_url ?? urlencode("https://google.com/search?q=$place_title"),
                    'lat' => $place_lat,
                    'lng' => $place_lng,
                ]);
                $data->mission_place()->create(['place_id' => $place->id]);
            }

            if ($content_code && $content_title && $content_channel) {
                $content = Content::updateOrCreate(['code' => $content_code], [
                    'type' => 'youtube',
                    'title' => $content_title,
                    'description' => $content_description,
                    'channel' => $content_channel,
                    'thumbnail_image' => $content_thumbnail_image,
                ]);
                $data->mission_content()->create(['content_id' => $content->id]);
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
                'missions.title', 'missions.subtitle', 'missions.description',
                'missions.is_event',
                DB::raw("missions.id <= 1213 and missions.is_event = 1 as is_old_event"), 'missions.event_type',
                'missions.is_ground',
                'missions.started_at', 'missions.ended_at',
                DB::raw("(missions.started_at is null or missions.started_at<='" . date('Y-m-d H:i:s') . "') and
                    (missions.ended_at is null or missions.ended_at>'" . date('Y-m-d H:i:s') . "') as is_available"),
                'missions.thumbnail_image', 'missions.success_count',
                'mission_stat_id' => MissionStat::select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->limit(1),
                'users.id as owner_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area_like(), 'users.greeting',
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
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                'bookmark_total' => MissionStat::withTrashed()->selectRaw("COUNT(distinct user_id)")
                    ->whereColumn('mission_id', 'missions.id'),
                'comment_total' => MissionComment::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'),
            ])
            ->withCount(['feeds' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }])
            ->with(['place', 'content'])
            ->first();

        if (is_null($data)) {
            return success([
                'result' => false,
                'reason' => 'not found mission',
            ]);
        }

        $data->owner = arr_group($data, ['owner_id', 'nickname', 'profile_image', 'gender', 'area', 'greeting', 'followers', 'is_following']);
        $data->product = arr_group($data, ['type', 'id', 'brand', 'title', 'image', 'url', 'price'], 'product_');

        $data->images = $data->images()->orderBy('order')->orderBy('id')->pluck('image');
        $data->areas = mission_areas($data->id)->pluck('name');

        $data->users = mission_users($mission_id, $user_id, true)->get();

        $feeds = $this->feed($request, $mission_id)['data'];

        if ($data->is_ground) {
            $data->images = $data->images()->select(['type', 'image'])->orderBy('order')->orderBy('id')->get();
            $data->ground = $data->ground()
                ->select([
                    'intro_video', 'logo_image', 'code_title', 'code', 'code_placeholder', 'code_image',
                    'goal_distance_title', 'goal_distances',
                ])
                ->first();
            $data->reward = $data->reward()->select(['title', 'image'])->first();
        }

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

        return success([
            'result' => true,
            'mission' => $data,
            // 'places' => $places,
            // 'products' => $products,
            'feeds_count' => $feeds['feeds_count'] ?? 0,
            'feeds' => $feeds['feeds'] ?? [],
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
                // $query->where('feeds.user_id', $user_id)->orWhere('feeds.is_hidden', false);
            })
            ->join('feeds', function ($query) use ($user_id) {
                $query->on('feeds.id', 'feed_missions.feed_id')
                    ->whereNull('feeds.deleted_at')
                    ->where(function ($query) use ($user_id) {
                        // $query->where('feeds.is_hidden', 0)->orWhere('feeds.user_id', $user_id);
                    });
            })
            ->join('users', 'users.id', 'feeds.user_id')
            ->select([
                'users.id as user_id', 'users.nickname', 'users.profile_image',
                'feeds.id', 'feeds.created_at', 'feeds.content', 'feeds.is_hidden',
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

    public function place_available($mission_id, $available = null): array
    {
        $subtitle = Mission::where('id', $mission_id)->value('subtitle');
        $places = Place::where('mission_places.mission_id', $mission_id)
            ->when($available, function ($query) {
                $query->whereDoesntHave('feeds', function ($query) {
                    $user_id = token()->uid;
                    $query->where('feeds.created_at', '>=', date('Y-m-d'));
                    $query->where('user_id', $user_id);
                });
            })
            ->join('mission_places', 'mission_places.place_id', 'places.id')
            ->select('places.*')
            ->distinct()
            ->get();

        return success([
            'result' => true,
            'places_count' => count($places),
            'subtitle' => $subtitle,
            'places' => $places,
        ]);
    }

    public function ground($mission_id): array
    {
        $user_id = token()->uid;

        DB::enableQueryLog();
        $data = MissionGround::where('mission_id', $mission_id)
            ->join('missions', function ($query) {
                $query->on('missions.id', 'mission_grounds.mission_id')->whereNull('deleted_at');
            })
            ->select([
                'mission_grounds.*', 'missions.started_at', 'missions.ended_at',
                DB::raw("(missions.started_at is null or missions.started_at<=now()) and
                    (missions.ended_at is null or missions.ended_at>now()) as is_available"),
            ])
            ->first();

        $date = date('Y-m-d H:i:s');

        $diff = abs(date_diff(new \DateTime(date('Y-m-d')), new \DateTime($data->started_at))->days);
        if ($data->is_available) {
            $data->ground_d_day_title = '함께하는 중';
            $data->ground_d_day_text = "{$diff}일차";
        } elseif ($data->started_at > $date) {
            $data->ground_d_day_title = '함께하기 전';
            $data->ground_d_day_text = "D - $diff";
        } else {
            $data->ground_d_day_title = '종료';
            $data->ground_d_day_text = "";
        }

        $data->ground_progress_present = match ($data->ground_progress_type) {
            'all_distance' => Feed::join('feed_missions', function ($query) use ($mission_id) {
                $query->on('feed_missions.feed_id', 'feeds.id')
                    ->where('mission_id', $mission_id);
            })->sum('distance'),
            default => null,
        };

        $data->users = match ($data->ground_users_type) {
            'recent_complete' => MissionStat::whereNotNull('mission_stats.completed_at')
                ->join('users', function ($query) {
                    $query->on('users.id', 'mission_stats.user_id')->whereNull('users.deleted_at');
                })
                ->select([
                    'users.id as user_id', 'users.nickname', 'users.profile_image',
                    'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                    'is_follow' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                        ->where('follows.user_id', $user_id),
                ])->take(20)->get(),
            default => null,
        };

        $data->record_progress_present = match ($data->record_progress_type) {
            'feeds_count' => Feed::whereHas('feed_missions', function ($query) use ($mission_id) {
                $query->where('mission_id', $mission_id);
            })->where('user_id', $user_id)->count(),
            default => null,
        };

        $data->my_feeds = Feed::whereHas('feed_missions', function ($query) use ($mission_id) {
            $query->where('mission_id', $mission_id);
        })
            ->where('user_id', $user_id)
            ->select([
                'id as feed_id', 'user_id',
                'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->orderBy('id')->limit(1),
                'type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->orderBy('id')->limit(1),
                'content as top_text', 'created_at as date',
                DB::raw("CONCAT(DATEDIFF(created_at,'{$data->started_at}')+1,'일차') as bottom_text"),
            ])
            ->orderBy('id', 'desc')
            ->get();

        $tmp = $data->cert_details;
        foreach ($tmp as $i => $item) {
            $tmp[$i]['text'] = code_replace($item['text'], ['value' => match ($item['type']) {
                'feeds_count' => Feed::whereHas('feed_missions', function ($query) use ($mission_id) {
                    $query->where('mission_id', $mission_id);
                })->where('user_id', $user_id)->count(),
                'total_distance' => Feed::whereHas('feed_missions', function ($query) use ($mission_id) {
                    $query->where('mission_id', $mission_id);
                })->where('user_id', $user_id)->sum('distance'),
                'goal_distance' => MissionStat::where('mission_id', $mission_id)->where('user_id', $user_id)->value('goal_distance'),
                default => '',
            }]);
        }
        $data->cert_details = $tmp;

        $replaces = Mission::where('missions.id', $mission_id)
            ->leftJoin('mission_stats', function ($query) {
                $query->on('mission_stats.mission_id', 'missions.id')->whereNull('mission_stats.ended_at');
            })
            ->select([
                'users_count' => MissionStat::selectRaw("COUNT(distinct user_id)")->whereColumn('mission_id', 'missions.id'),
                'all_distance' => Feed::selectRaw("CAST(IFNULL(SUM(distance),0) as signed)")->whereColumn('mission_id', 'missions.id')
                    ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id'),
                'today_all_distance' => Feed::selectRaw("CAST(IFNULL(SUM(distance),0) as signed)")->whereColumn('mission_id', 'missions.id')
                    ->where('feeds.created_at', '>=', date('Y-m-d'))
                    ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id'),
                DB::raw("IFNULL(goal_distance,0) as goal_distance"),
                'feeds_count' => Feed::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->join('feed_missions', 'feed_missions.feed_id', 'feeds.id'),
                'total_distance' => Feed::selectRaw("CAST(IFNULL(SUM(distance),0) as signed)")->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->join('feed_missions', 'feed_missions.feed_id', 'feeds.id'),
            ])
            ->first();
        $replaces->status_text = $data->record_progress_image_count > $replaces->feeds_count ? '도전 중' : '성공';
        $replaces = $replaces->toArray();

        foreach ($data->toArray() as $i => $item) {
            if (!is_string($item)) continue;
            $data[$i] = code_replace($item, $replaces);
        }

        $text = MissionGroundText::where('mission_id', $mission_id)->orderBy('order')->get()->groupBy('tab');
        $today_cert_count = Feed::where('feeds.created_at', '>=', date('Y-m-d'))
            ->where(FeedPlace::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), true)
            ->join('feed_missions', function ($query) use ($mission_id) {
                $query->on('feed_missions.feed_id', 'feeds.id')
                    ->where('feed_missions.mission_id', $mission_id);
            })
            ->distinct()
            ->count('user_id');
        $replaces = ['today_cert_count' => $today_cert_count];
        $data->ground_text = code_replace(mission_ground_text($text['ground'], $data->is_available, $mission_id, $user_id), $replaces);
        $data->record_text = code_replace(mission_ground_text($text['record'], $data->is_available, $mission_id, $user_id), $replaces);

        return DB::getQueryLog();

        return success([
            'ground' => $data,
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

            $content_code = $request->get('content_code');
            $content_title = $request->get('content_title');
            $content_description = $request->get('content_description');
            $content_channel = $request->get('content_channel_title');
            $content_thumbnail_image = $request->get('content_thumbnail_image');

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
            } elseif ($outside_product_id && $product_title && $product_price && $product_url) {
                $product = OutsideProduct::updateOrCreate(['url' => $product_url], [
                    'product_id' => $outside_product_id,
                    'image' => $product_image,
                    'brand' => $product_brand,
                    'title' => $product_title,
                    'price' => $product_price,
                ]);
                $mission->product()->updateOrCreate([], ['type' => 'outside', 'outside_product_id' => $product->id]);
            }


            if ($place_delete) {
                $mission->mission_place()->delete();
            } elseif ($place_address && $place_title) {
                $place = Place::updateOrCreate(['title' => $place_title, 'address' => $place_address], [
                    'description' => $place_description,
                    'image' => $place_image,
                    'url' => $place_url ?? urlencode("https://google.com/search?q=$place_title"),
                    'lat' => $place_lat,
                    'lng' => $place_lng,
                ]);
                $mission->mission_place()->updateOrCreate([], ['place_id' => $place->id]);
            }

            if ($content_code && $content_title && $content_channel) {
                $content = Content::updateOrCreate(['code' => $content_code], [
                    'type' => 'youtube',
                    'title' => $content_title,
                    'description' => $content_description,
                    'channel' => $content_channel,
                    'thumbnail_image' => $content_thumbnail_image,
                ]);
                $mission->mission_content()->updateOrCreate([], ['content_id' => $content->id]);
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

        $users = MissionStat::withTrashed()->where('mission_stats.mission_id', $mission_id)
            ->select([
                'mission_stats.user_id',
                'mission_feeds' => FeedMission::selectRaw("COUNT(1)")->whereColumn('mission_id', 'mission_stats.mission_id')
                    ->whereColumn('feeds.user_id', 'mission_stats.user_id')
                    ->join('feeds', function ($query) use ($user_id) {
                        $query->on('feeds.id', 'feed_missions.feed_id')
                            ->whereNull('feeds.deleted_at')
                            ->where(function ($query) use ($user_id) {
                                // $query->where('feeds.is_hidden', 0)->orWhere('feeds.user_id', $user_id);
                            });
                    }),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'mission_stats.user_id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'mission_stats.user_id')
                    ->where('user_id', $user_id),
            ])
            ->groupBy('mission_stats.user_id', 'mission_stats.mission_id')
            ->orderBy('mission_feeds', 'desc')
            ->orderBy('follower', 'desc')
            ->skip($page * $limit)->take($limit);

        $users = User::joinSub($users, 'u', 'u.user_id', 'users.id')
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area_like(),
                'u.mission_feeds', 'u.follower', 'u.is_following',
            ])
            ->orderBy('mission_feeds', 'desc')
            ->orderBy('follower', 'desc')
            ->orderBy('id', 'desc')->get();

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
        $user_id = token()->uid;//안씀, 내정보뿐만아니라 타 유저의 내역도 봐야함
        $user_id = $request->get('uid');
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

        $event_mission_info = Mission::where('missions.id', $mission_id)
            ->leftJoin('circlinDEV.CHALLENGE_INFO_2', 'CHALLENGE_INFO_2.CHALLINFO_PK', 'missions.id')
            ->leftJoin('mission_etc', 'mission_etc.mission_id', 'missions.id')
            ->leftJoin('mission_stats', 'mission_stats.mission_id', 'missions.id')
            ->leftJoin('users', 'users.id', 'mission_stats.user_id')
            ->leftJoin('circlinDEV.RUN_RANK', function ($query) use ($today) {
                $query->on('RUN_RANK.CHALL_PK', 'missions.id')
                    ->where([
                        'sex' => 'A',
                        'DEL_YN' => 'N',
                        'INS_DATE' => $today,
                    ]);
            })
            ->leftJoin('feed_missions', 'feed_missions.mission_stat_id', 'mission_stats.id')
            ->leftJoin('feeds', function ($query) {
                $query->on('feeds.id', 'feed_missions.feed_id')->whereNull('feeds.deleted_at');
            })
            ->select([
                'mission_stats.id as mission_stat_id', 'mission_stats.certification_image',
                'mission_stats.mission_id',
                DB::raw("CASE WHEN $mission_id ='1213' THEN '40000' ELSE '' END AS MAX_NUM"),
                'users.gender', 'users.nickname', 'users.profile_image', 'users.id as user_id',
                DB::raw("IFNULL(RUN_RANK.RANK,0) as RANK"),
                DB::raw("round(mission_stats.goal_distance - feeds.distance,3) as REMAIN_DIST"),
                'mission_stats.goal_distance', 'feeds.distance', 'feeds.laptime',
                'feeds.distance_origin', 'feeds.laptime_origin',
                'SCORE' => MissionStat::selectRaw("COUNT(user_id)")->whereColumn('user_id', 'users.id')
                    ->where('mission_id', $mission_id),
                DB::raw("CASE WHEN mission_stats.completed_at is null THEN '' ELSE '1' END as BONUS_FLAG"),
                DB::raw("CASE when $today between missions.reserve_started_at and missions.reserve_ended_at then 'R'
                      when $today between missions.started_at and missions.ended_at then 'Y'
                      ELSE 'N' end as STATE"),
                'FOLLOWER' => Follow::selectRaw("COUNT(user_id)")->where('target_id', $user_id),
                'CHALL_PARTI' => MissionStat::selectRaw("COUNT(user_id)")->where('mission_id', $mission_id),
                'missions.started_at as START_DATE', DB::raw("missions.ended_at + interval 1 day as END_DAY1"),
                DB::raw("(missions.started_at is null or missions.started_at<='" . date('Y-m-d H:i:s') . "') and
                    (missions.ended_at is null or missions.ended_at>'" . date('Y-m-d H:i:s') . "') as is_available"),
                'CERT_TODAY' => FeedMission::selectRaw("COUNT(*)")->whereColumn('mission_stat_id', 'mission_stats.id')
                    ->where('created_at', '>=', $today),
                'FINISH' => MissionStat::selectRaw("COUNT(*) > 0")->whereColumn('mission_id', 'missions.id')
                    ->whereNotNull('completed_at'),
                DB::raw("ifnull(   ( SELECT ifnull(YEST.RANK-TODAY.RANK,'0') CHANGED FROM
                    (SELECT b.RANK, b.USER_PK, a.TIER, a.NICKNAME, a.PROFILE_IMG, a.FOLLOWER
                    FROM circlinDEV.MEMBERDATA a, circlinDEV.RUN_RANK b
                    WHERE  a._ID=b.USER_PK
                    and USER_PK= $user_id  and INS_DATE=$today and DEL_YN='N' and b.SEX='A' and b.CHALL_ID= $mission_stat_id limit 0,1) TODAY
                    LEFT JOIN
                    (SELECT b.RANK, b.USER_PK, a.TIER, a.NICKNAME, a.PROFILE_IMG, a.FOLLOWER
                    FROM circlinDEV.MEMBERDATA a, circlinDEV.RUN_RANK b
                    WHERE
                    a._ID=b.USER_PK and USER_PK= $user_id  and INS_DATE= $today and DEL_YN='N'
                    and b.SEX='A' and b.CHALL_ID= $mission_stat_id limit 0,1)  YEST  on  TODAY.USER_PK=YEST.USER_PK
                    ),'') as CHANGED"),
                'mission_etc.bg_image', 'mission_etc.info_image_1', 'mission_etc.info_image_2', 'mission_etc.info_image_3',
                'mission_etc.info_image_4', 'mission_etc.info_image_5', 'mission_etc.info_image_6', 'mission_etc.info_image_7',
                'mission_etc.intro_image_1', 'mission_etc.intro_image_2', 'mission_etc.intro_image_3', 'mission_etc.intro_image_4',
                'mission_etc.intro_image_5', 'mission_etc.intro_image_6', 'mission_etc.intro_image_7', 'mission_etc.intro_image_8',
                'mission_etc.intro_image_9', 'mission_etc.intro_image_10',
                'mission_etc.subtitle_1', 'missions.description', 'mission_etc.subtitle_3', 'mission_etc.subtitle_4',
                'mission_etc.subtitle_5', 'mission_etc.subtitle_6', 'mission_etc.subtitle_7',
                'mission_etc.ai_text1', 'mission_etc.ai_text2',
            ])
            ->distinct()
            ->orderBy('mission_stats.id', 'desc')
            ->take(1)
            ->get();

        if ($mission_id == 1701) {
            $recent_user = FeedMission::where('feed_missions.mission_id', $mission_id)
                ->join('feeds', function ($query) {
                    $query->on('feeds.id', 'feed_missions.feed_id')->whereNull('deleted_at');
                })
                ->join('users', 'users.id', 'feeds.user_id')
                ->select([
                    'users.id as user_id', 'users.nickname', 'users.profile_image',
                    'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                    'follow_yn' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                        ->where('follows.user_id', $user_id),
                ])
                ->groupBy('users.id')
                ->orderBy(DB::raw("MAX(feed_missions.created_at)"), 'desc')
                ->take(15)
                ->get();
        } else {
            $recent_user = MissionStat::where('mission_stats.mission_id', $mission_id)
                ->join('users', 'users.id', 'mission_stats.user_id')
                ->select([
                    'users.id as user_id', 'users.nickname', 'users.profile_image',
                    'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                    'follow_yn' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                        ->where('follows.user_id', $user_id),
                ])
                ->groupBy('users.id')
                ->orderBy(DB::raw("MAX(mission_stats.created_at)"), 'desc')
                ->take(15)
                ->get();
        }

        // 참가자 총 거리
        $total_km = FeedMission::where('mission_id', $mission_id)
            ->leftJoin('feeds', function ($query) {
                $query->on('feeds.id', 'feed_missions.feed_id')->whereNull('feeds.deleted_at');
            })->sum('distance');

        // 내 기록
        $myRecord = Feed::where('missions.id', $mission_id)
            ->where('users.id', $user_id)
            ->join('users', 'users.id', 'feeds.user_id')
            ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id')
            ->join('missions', 'missions.id', 'feed_missions.mission_id')
            ->leftJoin('mission_stats', 'mission_stats.id', 'feed_missions.mission_stat_id')
            ->leftJoin('feed_places', 'feed_places.feed_id', 'feeds.id')
            ->leftJoin('places', 'places.id', 'feed_places.place_id')
            ->select([
                'feeds.user_id', 'feeds.content', 'feeds.created_at', 'feeds.id as feed_id',
                'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                'type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                'feeds.distance', 'feeds.laptime', 'mission_stats.goal_distance',
                'places.title as place_title', 'places.address as place_address', 'places.image as place_image', 'places.url as place_url',
            ])
            ->orderBy('feeds.id', 'desc')
            ->get();

        // 내 미션 상태
        $mission_stat = DB::select('select count(b.id) as day_count, ifnull(round(avg(a.distance),2),0) as distance,
                ifnull(sum(a.distance),0) total_distance,
                ifnull(ROUND((sum(a.distance) / c.goal_distance) * 100 ,0),0) as progress,
                sum( CASE WHEN cast(c.goal_distance as unsigned ) <= cast(a.distance as unsigned) then  1 else 0 end ) as success_today,
                ifnull((select count(id) from feed_missions where mission_id= ? ) ,0) cert_count,
                ifnull((select count(id) from feed_missions where mission_id= ? and created_at >= ?) ,0) today_cert_count
            from feeds a
            left join feed_missions b on a.id=b.feed_id
            left join mission_stats c on b.mission_id=c.mission_id and b.mission_stat_id=c.id  and b.mission_stat_id= ?
            where a.user_id= ?
                and b.mission_id= ?
                and a.deleted_at is null
            GROUP BY  a.distance, c.goal_distance
            union
            select 0 as day_count, 0 as distance, 0 as total_distance, 0 as progress, 0 as success_today,
                (select count(feeds.id) from feed_missions join feeds on feeds.id=feed_id and feeds.deleted_at is null where mission_id=?) cert_count,
                (select count(feeds.id) from feed_missions join feeds on feeds.id=feed_id and feeds.deleted_at is null where mission_id=? and feeds.created_at >= ?) today_cert_count
            limit 1',
            [$mission_id, $mission_id, date('Y-m-d'), $mission_stat_id, $user_id, $mission_id, $mission_id, $mission_id, date('Y-m-d')]);

        $place_info = MissionPlace::where('mission_id', $mission_id)
            ->join('places', 'places.id', 'mission_places.place_id')
            ->select([
                'mission_places.mission_id', 'mission_places.place_id',
                'places.address', 'places.title', 'places.description', 'places.image', 'places.url',
            ])
            ->get();

        $ai_text1 = new Collection(json_decode($event_mission_info[0]->ai_text1));
        $ai_text2 = new Collection(json_decode($event_mission_info[0]->ai_text2));

        $today_users_count = Feed::where('feeds.created_at', '>=', date('Y-m-d'))
            ->where(FeedPlace::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), true)
            ->join('feed_missions', function ($query) use ($mission_id) {
                $query->on('feed_missions.feed_id', 'feeds.id')
                    ->where('feed_missions.mission_id', $mission_id);
            })
            ->distinct()
            ->count('user_id');

        $replaces = [
            'today_users_count' => $today_users_count,
        ];

        $AiText = code_replace(mission_ground_text($ai_text1, $event_mission_info[0]->is_available, $mission_id, $user_id), $replaces);
        $AiText2 = code_replace(mission_ground_text($ai_text2, $event_mission_info[0]->is_available, $mission_id, $user_id), $replaces);

        return success([
            'success' => true,
            'event_mission_info' => $event_mission_info,
            'recent_user' => $recent_user,
            'total_km' => $total_km,
            'myRecord' => $myRecord,
            'mission_stat' => $mission_stat,
            'place_info' => $place_info,
            'AiText' => $AiText,
            'AiText2' => $AiText2,
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
            (select count(user_id) from mission_stats where mission_id= ? and ended_at is null) as participants,
            (select count(user_id) from mission_likes where mission_id= ? and deleted_at is null) as likes,

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
        ifnull((SELECT "Y" FROM mission_likes n WHERE user_id= ? and a.id=n.mission_id limit 1),"N" )as like_yn ,
        ifnull((SELECT id FROM mission_stats WHERE user_id= ? and ended_at is null and completed_at is null and mission_id= ? limit 1),"" ) as mission_stat_id,
            CASE when date_add(SYSDATE() , interval + 9 hour ) between a.reserve_started_at and a.reserve_ended_at then "PRE"
                            when date_add(SYSDATE() , interval + 9 hour ) between a.started_at and a.ended_at then "START"
                            ELSE "END" end as CHECK_START
                            , d.title as reward_name
                            , d.id as reward_id
                            , d.image as reward_image

            from   missions a
					LEFT JOIN mission_etc c on  a.id=c.mission_id
					LEFT JOIN mission_rewards d on a.id = d.mission_id,  `users` as owner
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

        if (is_null($mission_id)) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        if (MissionStat::where(['user_id' => $user_id, 'mission_id' => $mission_id])->exists()) {
            return success(['result' => false, 'reason' => 'already bookmark']);
        } elseif (Mission::select(DB::raw("(missions.reserve_started_at is null or missions.reserve_started_at<='" . date('Y-m-d H:i:s') . "') and
            (missions.reserve_ended_at is null or missions.reserve_ended_at>'" . date('Y-m-d H:i:s') . "') or
            (missions.started_at is null or missions.started_at<='" . date('Y-m-d H:i:s') . "') and
            (missions.ended_at is null or missions.ended_at>'" . date('Y-m-d H:i:s') . "') as is_available"))
            ->where('id', $mission_id)->value('is_available')) {
            $data = MissionStat::create([
                'user_id' => $user_id,
                'mission_id' => $mission_id,
            ]);

            $user_count = MissionStat::where('mission_id', $mission_id)->count();

            // 조건별 푸시
            $pushes = MissionPush::where('mission_id', $mission_id)->get();
            if (count($pushes) > 0) {
                foreach ($pushes as $push) {
                    if ($push->type === 'bookmark' && $push->value > 0) {
                        PushController::send_mission_push($push, $user_id, $mission_id);
                    }
                }
            }
            return success([
                'success' => true,
                'mission_stat_id' => $data->id,
                'user_count' => $user_count,
            ]);
        } else {
            return success(['result' => false]);
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
            $participant_list = MissionStat::where('mission_stats.mission_id', $mission_id)
                ->join('users', 'users.id', 'mission_stats.user_id')
                ->select([
                    'users.id as user_id', 'users.nickname', 'users.profile_image',
                    'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                    'follow_yn' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                        ->where('follows.user_id', $user_id),
                ])
                ->groupBy('users.id')
                ->orderBy(DB::raw("MAX(mission_stats.created_at)"), 'desc')
                ->get();

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
                    $certification_image = DB::update('UPDATE mission_stats set certification_image = ? where id = ? ;'
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
        $min_feed_id = $request->get('min_feed_id');
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

        $double_zone_feed = Feed::where('missions.id', $mission_id)
            ->when(1, function ($query) use ($type, $mission_id, $place_id) {
                if ($type == 'ALL') {
                    //$query->whereNotIn('places.id', MissionPlace::select('place_id')->where('mission_id', $mission_id));
                } elseif ($type == 'ETC') {
                    $query->whereNotIn('places.id', MissionPlace::select('place_id')->where('mission_id', $mission_id));
                } else {
                    $query->where('places.id', $place_id);
                }
            })
            ->where(function ($query) use ($mission_id) {
                $query->whereNull('places.id')
                    ->orWhereIn('places.id', MissionPlace::select('place_id')->where('mission_id', $mission_id));
            })
            ->where(function ($query) use ($user_id) {
                $query->where('is_hidden', 0)
                    ->orWhere('feeds.user_id', $user_id);
            })
            ->join('users', 'users.id', 'feeds.user_id')
            ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id')
            ->join('missions', 'missions.id', 'feed_missions.mission_id')
            ->join('mission_stats', 'mission_stats.id', 'feed_missions.mission_stat_id')
            ->leftJoin('feed_places', 'feed_places.feed_id', 'feeds.id')
            ->leftJoin('places', 'places.id', 'feed_places.place_id')
            ->select([
                'feeds.user_id', 'feeds.content', 'feeds.created_at', 'feeds.id as feed_id',
                'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('order')->limit(1),
                'users.profile_image', 'users.nickname', 'feeds.id',
                'feeds.distance', 'feeds.laptime', 'mission_stats.goal_distance',
                'places.title as place_title', 'places.address as place_address', 'places.image as place_image', 'places.url as place_url', 'places.id as place_id',
                'check_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')->where('user_id', $user_id),
                'has_comment' => FeedComment::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')->where('user_id', $user_id),
                DB::raw("case when feed_places.place_id is null then null else '1' end as has_place"),
                'has_product' => FeedProduct::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'),
            ])
            ->orderBy('feeds.id', 'desc')
            ->get();

        return success([
            'success' => true,
            'double_zone_feed' => $double_zone_feed,
        ]);
    }
}
