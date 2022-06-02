<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatUser;
use App\Models\Feed;
use App\Models\FeedComment;
use App\Models\FeedImage;
use App\Models\FeedLike;
use App\Models\FeedMission;
use App\Models\FeedProduct;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionGround;
use App\Models\MissionPush;
use App\Models\MissionStat;
use App\Models\MissionTreasurePoint;
use App\Models\Order;
use App\Models\OutsideProduct;
use App\Models\Place;
use App\Models\PointHistory;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;


class FeedController extends Controller
{
    public function store(Request $request): array
    {
        $user_id = token()->uid;

        $files = $request->file('files');
        $content = $request->get('content');
        $missions = array_unique(Arr::wrap($request->get('missions')));
        $is_hidden = $request->get('is_hidden', 0);
        $distance = $request->get('distance');
        $laptime = $request->get('laptime');
        $distance_origin = $request->get('distance_origin');
        $laptime_origin = $request->get('laptime_origin');

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

        if (!$content || !$files) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        $require_place = Mission::whereIn('id', $missions)->where('is_require_place', true)->exists();
        if ($require_place && is_null($place_title)) {
            abort(403, '해당 미션은 장소를 꼭 인증해야 합니다.');
        }

        $not_duplicate_missions = Mission::whereIn('id', $missions)
            ->where('is_not_duplicate_place', true)->pluck('id');
        foreach ($not_duplicate_missions as $mission_id) {
            $feed_places = Feed::join('feed_places', 'feed_id', 'feeds.id')
                ->join('places', 'places.id', 'place_id')
                ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id')
                ->where('feeds.created_at', '>=', date('Y-m-d'))
                ->where('mission_id', $mission_id)
                ->where('user_id', $user_id)
                ->pluck('places.title')
                ->toArray();
            if (in_array($place_title, $feed_places)) {
                return success([
                    'result' => false,
                    'reason' => '동일한 장소 인증은 1일 1회만 가능합니다.',
                    'message' => '동일한 장소 인증은 1일 1회만 가능합니다.',
                ]);
                abort(403, '동일한 장소 인증은 1일 1회만 가능합니다.');
            }
        }

        $grounds = MissionGround::select([
            'mission_grounds.distance_min',
            'mission_grounds.distance_max',
            'mission_grounds.goal_distance_text',
        ])
            ->join('mission_stats', 'mission_stats.mission_id', 'mission_grounds.mission_id')
            ->whereIn('mission_grounds.mission_id', $missions)
            ->get();

        foreach ($grounds as $ground) {
            if (!is_null($ground->distance_min)) {
                if ($distance < $ground->distance_min) {
                    return success([
                        'result' => false,
                        'message' => "최소 $ground->distance_min$ground->goal_distance_text 부터 입력이 가능합니다.",
                    ]);
                }
            }
            if (!is_null($ground->distance_max)) {
                if ($distance > $ground->distance_max) {
                    return success([
                        'result' => false,
                        'message' => "최대 $ground->distance_max$ground->goal_distance_text 까지 입력이 가능합니다.",
                    ]);
                }
            }
        }

        try {
            DB::beginTransaction();

            $feed = Feed::create([
                'user_id' => $user_id,
                'content' => $content,
                'distance' => is_null($distance) ? $distance : floatval($distance),
                'laptime' => is_null($laptime) ? $laptime : floatval($laptime),
                'distance_origin' => is_null($distance_origin) ? $distance_origin : floatval($distance_origin),
                'laptime_origin' => is_null($laptime_origin) ? $laptime_origin : floatval($laptime_origin),
                'is_hidden' => $is_hidden == 1,
            ]);

            // 이미지 및 동영상 업로드
            if ($files) {
                foreach (Arr::wrap($files) as $i => $file) {
                    $uploaded_thumbnail = '';
                    if (str_starts_with($file->getMimeType(), 'image/')) {
                        $type = 'image';

                        $image = Image::make($file->getPathname());
                        /*if ($image->width() > $image->height()) {
                            $x = ($image->width() - $image->height()) / 2;
                            $y = 0;
                            $src = $image->height();
                        } else {
                            $x = 0;
                            $y = ($image->height() - $image->width()) / 2;
                            $src = $image->width();
                        }
                        $image->crop($src, $src, round($x), round($y));*/

                        $image->orientate();

                        $tmp_path = "{$file->getPath()}/{$user_id}_" . Str::uuid() . ".{$file->extension()}";
                        $image->save($tmp_path);
                        $uploaded_file = Storage::disk('s3')->put("/SNS/$user_id", new File($tmp_path));
                        @unlink($tmp_path);
                    } elseif (str_starts_with($file->getMimeType(), 'video/')) {
                        $type = 'video';
                        $uploaded_file = Storage::disk('s3')->put("/SNS/$user_id", $file);

                        $thumbnail = "Image/SNS/$user_id/thumb_" . $file->hashName();

                        $host = config('filesystems.disks.ftp3.host');
                        $username = config('filesystems.disks.ftp3.username');
                        $password = config('filesystems.disks.ftp3.password');
                        $url = image_url($uploaded_file);
                        $url2 = image_url($thumbnail);
                        if (uploadVideoResizing($user_id, $host, $username, $password, $url, $url2, $feed->id)) {
                            $uploaded_thumbnail = $thumbnail;
                        }
                    } else {
                        continue;
                    }

                    FeedImage::create([
                        'feed_id' => $feed->id,
                        'order' => $i,
                        'type' => $type,
                        'image' => image_url($uploaded_file),
                        'thumbnail_image' => image_url($uploaded_thumbnail ?: $uploaded_file),
                    ]);
                }
            }

            // 미션 적용
            $completed_missions = [];
            if ($missions) {
                foreach ($missions as $mission_id) {
                    $stat = MissionStat::where(['user_id' => $user_id, 'mission_id' => $mission_id])
                        ->orderBy('id', 'desc')->first();
                    if (is_null($stat)) continue;

                    $mission = Mission::where('missions.id', $mission_id)
                        ->leftJoin('mission_grounds', 'mission_grounds.mission_id', 'missions.id')
                        ->select(['missions.*', 'mission_grounds.goal_distance_type'])
                        ->first();
                    $feeds_count = Feed::where('feeds.user_id', $user_id)
                        ->where('feed_missions.mission_id', $mission_id)
                        ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id')
                        ->count();

                    $need_review = $mission->is_refund && $feeds_count === 0;

                    if ($mission->is_ground) {
                        if ($mission->success_count === 0 || $feeds_count >= $mission->success_count) {
                            $feeds_distance = Feed::where('feeds.user_id', $user_id)
                                ->where('feed_missions.mission_id', $mission_id)
                                ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id')
                                ->sum('distance');

                            if (($mission->goal_distance_type === 'goal' && $feeds_distance >= $stat->goal_distance) ||
                                ($mission->goal_distance_type === 'min' && $distance >= $stat->goal_distance)
                            ) {
                                $completed_missions[] = $mission_id;

                                if (is_null($stat->completed_at)) {
                                    $stat->update(['completed_at' => date('Y-m-d H:i:s')]);
                                }
                                NotificationController::send($user_id, 'mission_complete', null, $mission_id);
                            }
                        }
                    } elseif ($mission->success_count === 1 && $feeds_count === 0) {
                        $completed_missions[] = $mission_id;

                        if (is_null($stat->completed_at)) {
                            $stat->update(['completed_at' => date('Y-m-d H:i:s')]);
                        }
                        NotificationController::send($user_id, 'mission_complete', null, $mission_id);
                    }

                    // 보물찾기 보상
                    if ($mission->treasure_started_at <= date('Y-m-d H:i:s') &&
                        $mission->treasure_ended_at > date('Y-m-d H:i:s') && !$is_hidden &&
                        FeedMission::where('mission_id', $mission_id)->where(Feed::withTrashed()->select('user_id')
                            ->whereColumn('feeds.id', 'feed_missions.feed_id'), $user_id)
                            ->where('created_at', '>=', date('Y-m-d'))->doesntExist()
                    ) {
                        // 랜덤 보상 가져오기
                        $reward_points = MissionTreasurePoint::where('mission_id', $mission_id)
                            ->orderBy(DB::raw("(point_min+point_max)/2"))
                            ->get();

                        if (count($reward_points)) {
                            // 랜덤 보상 개수만큼 배열에 담기
                            $rewards = [];
                            foreach ($reward_points as $point) {
                                for ($i = 0; $i < $point->qty; $i++) {
                                    $rewards[] = [$point->point_min, $point->point_max, $point->id];
                                }
                            }
                            // 남은 보상 없는 경우 제일 저렴한 보상으로
                            if (count($rewards) === 0) {
                                $rewards[] = [
                                    $reward_points[0]->point_min,
                                    $reward_points[0]->point_max,
                                    $reward_points[0]->id,
                                ];
                            }

                            // 포인트 랜덤뽑기
                            $tmp = $rewards[random_int(0, count($rewards) - 1)];
                            $point = round(random_int($tmp[0], $tmp[1]), -1);
                            MissionTreasurePoint::where(['id' => $tmp[2], 'is_stock' => true])->decrement('qty');
                            MissionTreasurePoint::where(['id' => $tmp[2]])->increment('count');

                            // 포인트 지급
                            PointController::change_point($user_id, $point, 'mission_treasure', 'mission', $mission_id);
                            NotificationController::send($user_id, 'mission_treasure', null, $mission_id, false, ['point' => $point]);

                            $treasure_reward = $point;
                        }
                    }

                    FeedMission::create([
                        'feed_id' => $feed->id,
                        'mission_stat_id' => $stat->id,
                        'mission_id' => $mission_id,
                    ]);

                    #region 미션 푸시 발송
                    $pushes = MissionPush::where('mission_id', $mission_id)
                        ->where(function ($query) {
                            $query->where('is_disposable', true)->where('count', 0)
                                ->orWhere('is_disposable', false);
                        })
                        ->get();
                    if (count($pushes) > 0) {
                        foreach ($pushes->groupBy('type') as $type => $pushes) {
                            if ($type === 'feed_upload_count' || $type === 'first_feed_upload') {
                                $count = Feed::where('feeds.user_id', $user_id)
                                    // ->where(FeedPlace::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), true)
                                    ->join('feed_missions', function ($query) use ($mission_id) {
                                        $query->on('feed_missions.feed_id', 'feeds.id')
                                            ->where('feed_missions.mission_id', $mission_id);
                                    })
                                    ->distinct()
                                    ->count('feeds.id');
                                foreach ($pushes as $push) {
                                    if ($count == $push->value) {
                                        PushController::send_mission_push($push, $user_id, $mission_id);
                                    }
                                }
                            } elseif ($type === 'feed_upload_complete') {
                                $is_min = MissionGround::where('mission_id', $mission_id)
                                        ->value('goal_distance_type') === 'min';
                                $count = Feed::join('feed_missions', 'feed_missions.feed_id', 'feeds.id')
                                    ->where('feed_missions.mission_id', $mission_id)
                                    ->where('feeds.user_id', $user_id)
                                    ->when($is_min, function ($query) {
                                        $query->where('feeds.created_at', '>=', date('Y-m-d'));
                                    })
                                    ->sum('distance') >= $stat->goal_distance ? 1 : 0;

                                foreach ($pushes as $push) {
                                    if ($count == $push->value) {
                                        PushController::send_mission_push($push, $user_id, $mission_id);
                                    }
                                }
                            } elseif ($type === 'users_count') {
                                $count = Feed::/*where(FeedPlace::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), true)
                                    ->*/ join('feed_missions', function ($query) use ($mission_id) {
                                    $query->on('feed_missions.feed_id', 'feeds.id')
                                        ->where('feed_missions.mission_id', $mission_id);
                                })
                                    ->distinct()
                                    ->count('user_id');
                                foreach ($pushes as $push) {
                                    if ($count >= $push->value) {
                                        PushController::send_mission_push($push, $user_id, $mission_id);
                                    }
                                }
                            } elseif ($type === 'feeds_count') {
                                $count = Feed::/*where(FeedPlace::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), true)
                                    ->*/ join('feed_missions', function ($query) use ($mission_id) {
                                    $query->on('feed_missions.feed_id', 'feeds.id')
                                        ->where('feed_missions.mission_id', $mission_id);
                                })
                                    ->distinct()
                                    ->count('feeds.id');
                                foreach ($pushes as $push) {
                                    if ($count >= $push->value) {
                                        PushController::send_mission_push($push, $user_id, $mission_id);
                                    }
                                }
                            }
                        }
                    }
                    #endregion
                }

                /*foreach ($completed_missions as $mission_id) {
                    NotificationController::send($user_id, 'mission_complete', null, $mission_id);
                }*/
            }

            // 제품이나 장소 등록 시 50포인트 씩 제공
            $point = PointHistory::where('user_id', $user_id)
                ->whereIn('reason', ['feed_upload_place', 'feed_upload_product'])
                ->where('point', '>', 0)
                ->where('created_at', '>=', init_today())
                ->sum('point');

            $product_reward = false;
            $place_reward = false;
            if ($product_id) {
                FeedProduct::create([
                    'feed_id' => $feed->id,
                    'type' => 'inside',
                    'product_id' => $product_id,
                ]);

                /*if ($point < 500) {
                    PointController::change_point($user_id, 50, 'feed_upload_product', 'feed', $feed->id);
                    NotificationController::send($user_id, 'feed_upload_product', null, $feed->id, false,
                        ['point' => 50, 'point2' => 500 - $point - 50]);
                    $point += 50;
                    $product_reward = true;
                }*/
            } elseif ($outside_product_id && $product_title && $product_price && $product_url) {
                $product = OutsideProduct::updateOrCreate(['product_id' => $outside_product_id], [
                    'image' => $product_image,
                    'brand' => $product_brand,
                    'title' => $product_title,
                    'price' => $product_price,
                    'url' => $product_url,
                ]);
                $feed->product()->updateOrCreate([], ['type' => 'outside', 'outside_product_id' => $product->id]);

                /*if ($point < 500) {
                    PointController::change_point($user_id, 50, 'feed_upload_product', 'feed', $feed->id);
                    NotificationController::send($user_id, 'feed_upload_product', null, $feed->id, false,
                        ['point' => 50, 'point2' => 500 - $point - 50]);
                    $point += 50;
                    $product_reward = true;
                }*/
            }

            if ($place_address && $place_title) {
                $place = Place::firstOrCreate(['title' => $place_title, 'address' => $place_address], [
                    'description' => $place_description,
                    'image' => $place_image,
                    'url' => $place_url ?? urlencode("https://google.com/search?q=$place_title"),
                    'lat' => $place_lat,
                    'lng' => $place_lng,
                ]);
                $feed->feed_place()->create(['place_id' => $place->id]);

                /*if ($point < 500) {
                    PointController::change_point($user_id, 50, 'feed_upload_place', 'feed', $feed->id);
                    NotificationController::send($user_id, 'feed_upload_place', null, $feed->id, false,
                        ['point' => 50, 'point2' => 500 - $point - 50]);
                    $point += 50;
                    $place_reward = true;
                }*/
            }

            // 조건별 푸시
            if (count($missions)) {
                foreach ($missions as $mission_id) {
                }
            }

            DB::commit();

            return success([
                'result' => true,
                'feed' => $feed,
                'completed_missions' => $completed_missions ?? null,
                'product_reward' => $product_reward,
                'place_reward' => $place_reward,
                'treasure_reward' => $treasure_reward ?? 0,
                'need_review' => $need_review ?? false,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function show($id): array
    {
        $user_id = token()->uid;

        $feed = Feed::where('feeds.id', $id)
            ->join('users', 'users.id', 'feeds.user_id')
            ->leftJoin('feed_products', 'feed_products.feed_id', 'feeds.id')
            ->leftJoin('products', 'products.id', 'feed_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'feed_products.outside_product_id')
            ->select([
                'feeds.id',
                'feeds.created_at',
                'feeds.content',
                'feeds.is_hidden',
                'users.id as user_id',
                'users.nickname',
                'users.profile_image',
                'users.gender',
                'area' => area_like(),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
                'feed_products.type as product_type',
                DB::raw("IF(feed_products.type='inside', feed_products.product_id, feed_products.outside_product_id) as product_id"),
                DB::raw("IF(feed_products.type='inside', brands.name_ko, outside_products.brand) as product_brand"),
                DB::raw("IF(feed_products.type='inside', products.name_ko, outside_products.title) as product_title"),
                DB::raw("IF(feed_products.type='inside', products.thumbnail_image, outside_products.image) as product_image"),
                'outside_products.url as product_url',
                'place_address' => Place::select('address')->whereColumn('feed_places.feed_id', 'feeds.id')
                    ->join('feed_places', 'feed_places.place_id', 'places.id')
                    ->orderBy('feed_places.id')->limit(1),
                'place_title' => Place::select('title')->whereColumn('feed_places.feed_id', 'feeds.id')
                    ->join('feed_places', 'feed_places.place_id', 'places.id')
                    ->orderBy('feed_places.id')->limit(1),
                'place_description' => Place::select('description')->whereColumn('feed_places.feed_id', 'feeds.id')
                    ->join('feed_places', 'feed_places.place_id', 'places.id')
                    ->orderBy('feed_places.id')->limit(1),
                'place_image' => Place::select('image')->whereColumn('feed_places.feed_id', 'feeds.id')
                    ->join('feed_places', 'feed_places.place_id', 'places.id')
                    ->orderBy('feed_places.id')->limit(1),
                'place_url' => Place::select('url')->whereColumn('feed_places.feed_id', 'feeds.id')
                    ->join('feed_places', 'feed_places.place_id', 'places.id')
                    ->orderBy('feed_places.id')->limit(1),
                'check_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'emoji_total' => ChatUser::selectRaw("COUNT(distinct chat_messages.chat_room_id)")->withTrashed()
                    ->whereColumn('chat_users.user_id', 'feeds.user_id')
                    ->whereColumn('chat_messages.feed_id', 'feeds.id')
                    ->join('chat_messages', function ($query) {
                        $query->on('chat_messages.chat_room_id', 'chat_users.chat_room_id')
                            ->whereColumn('chat_messages.user_id', '!=', 'chat_users.user_id');
                    })->whereNotNull('message'),
                'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                    ->where('user_id', token()->uid), // 해당 피드에 체크를 남겼는가
                'has_comment' => FeedComment::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                    ->where('user_id', token()->uid), // 해당 피드에 댓글을 남겼는가
                'has_emoji' => ChatMessage::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                    ->whereNotNull('message') // 피드 공유와 반응 남김을 구분을 위해
                    ->where('user_id', $user_id)->whereHas('room', function ($query) {
                        $query->where('is_group', false)->whereHas('users', function ($query) {
                            $query->whereColumn('user_id', 'feeds.user_id');
                        });
                    }), // 해당 피드로 이모지를 보낸 적이 있는가
            ])
            ->with('users', function ($query) {
                $query->join('users', 'users.id', 'feed_likes.user_id')
                    ->select(['feed_id', 'users.id', 'users.nickname', 'users.profile_image', 'users.gender'])
                    ->orderBy('feed_likes.id', 'desc')
                    ->take(2);
            })
            ->first();

        if (is_null($feed)) {
            return success([
                'result' => false,
                'reason' => 'not found',
            ]);
        }

        $feed['product'] = arr_group($feed, ['type', 'id', 'brand', 'title', 'image', 'url', 'price'], 'product_');
        $feed['place'] = arr_group($feed, ['address', 'title', 'description', 'image', 'url'], 'place_');

        $feed['images'] = $feed->images()->select(['type', 'image'])->orderBy('order')->get();

        $feed['missions'] = $feed->missions()->select([
            'missions.id',
            'mission_categories.emoji',
            'missions.title',
            'missions.is_event',
            DB::raw("missions.id <= 1213 and missions.is_event = 1 as is_old_event"),
            'missions.event_type',
            'missions.is_ground',
            'missions.is_ocr',
            'missions.started_at',
            'missions.ended_at',
            'missions.thumbnail_image',
            'missions.success_count',
            'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->whereColumn('mission_id', 'missions.id')
                ->where('user_id', $user_id),
            'mission_stat_id' => MissionStat::withTrashed()->select('id')->whereColumn('mission_id', 'missions.id')
                ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
            'mission_stat_user_id' => MissionStat::withTrashed()
                ->select('user_id')
                ->whereColumn('mission_id', 'missions.id')
                ->where('user_id', $user_id)
                ->orderBy('id', 'desc')
                ->limit(1),
            DB::raw("$user_id as mission_stat_user_id"),
        ])
            ->join('mission_categories', 'mission_categories.id', 'missions.mission_category_id')
            ->with('refundProducts', fn($query) => $query->select([
                'products.id',
                'products.code',
                'products.name_ko',
                'products.thumbnail_image',
                'mission_refund_products.limit',
                'current' => Order::selectRaw("COUNT(distinct orders.id)")
                    ->join('order_products', 'order_id', 'orders.id')
                    ->whereColumn('product_id', 'products.id'),

                'products.shipping_fee',
                'products.id as product_id',
                'brands.name_ko as brand_name',
                'products.name_ko as product_name',
                'products.price',
                'products.sale_price',
                'products.status',
                DB::raw("CAST(100 - ROUND(products.sale_price / products.price * 100) as char) as discount_rate"),
                DB::raw("'N' as CART_YN"),
                DB::raw("1 as qty"),
                DB::raw("'' as opt_name1"),
                DB::raw("'' as opt_name2"),
                DB::raw("'' as opt_name3"),
                DB::raw("'' as opt_name4"),
                DB::raw("'' as opt_name5"),
                DB::raw("0 as opt_price1"),
                DB::raw("0 as opt_price2"),
                DB::raw("0 as opt_price3"),
                DB::raw("0 as opt_price4"),
                DB::raw("0 as opt_price5"),
                DB::raw("'' as opt1"),
                DB::raw("'' as opt2"),
                DB::raw("'' as opt3"),
                DB::raw("'' as opt4"),
                DB::raw("'' as opt5"),
            ])->join('brands', 'brands.id', 'products.brand_id'))
            ->get();

        return success([
            'result' => true,
            'feed' => $feed,
        ]);
    }

    public function edit($feed_id): array
    {
        $user_id = token()->uid;

        $feed = Feed::where('id', $feed_id)
            ->select(['id', 'user_id', 'content', 'is_hidden'])
            ->with('place', function ($query) {
                $query->select(['feed_id', 'title', 'address', 'description', 'image', 'url']);
            })
            ->with('product', function ($query) {
                $query->select(['feed_id', 'type', 'product_id', 'brand', 'title', 'image', 'url', 'price']);
            })
            ->first();

        if (is_null($feed)) {
            return success([
                'result' => false,
                'reason' => 'not found feed',
            ]);
        }

        if ($feed->user_id != $user_id) {
            return success([
                'result' => false,
                'reason' => 'access denied',
            ]);
        }

        return success([
            'result' => true,
            'feed' => $feed,
        ]);
    }

    public function update(Request $request, $feed_id): array
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

            $feed = Feed::where('id', $feed_id)->first();

            if (is_null($feed)) {
                return success([
                    'result' => false,
                    'reason' => 'not found feed',
                ]);
            }

            if ($feed->user_id != $user_id) {
                return success([
                    'result' => false,
                    'reason' => 'access denied',
                ]);
            }

            $content = trim($request->get('content'));

            $is_hidden = $request->get('is_hidden');

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

            $update_data = [];
            if (isset($content) && $content !== '') {
                $update_data['content'] = $content;
            }
            if (isset($is_hidden)) {
                $update_data['is_hidden'] = $is_hidden;
            }
            if (count($update_data)) {
                $feed->update($update_data);
            }

            if ($product_delete) {
                $feed->product()->delete();
            } elseif ($product_id) {
                $feed->product()->updateOrCreate([], [
                    'type' => 'inside',
                    'product_id' => $product_id,
                ]);
            } elseif ($outside_product_id && $product_title && $product_price && $product_url) {
                $product = OutsideProduct::updateOrCreate(['product_id' => $outside_product_id], [
                    'image' => $product_image,
                    'brand' => $product_brand,
                    'title' => $product_title,
                    'price' => $product_price,
                    'url' => $product_url,
                ]);
                $feed->product()->updateOrCreate([], ['type' => 'outside', 'outside_product_id' => $product->id]);
            }

            if ($place_delete) {
                $feed->feed_place()->delete();
            } elseif ($place_address && $place_title) {
                $place = Place::firstOrCreate(['title' => $place_title, 'address' => $place_address], [
                    'description' => $place_description,
                    'image' => $place_image,
                    'url' => $place_url ?? urlencode("https://google.com/search?q=$place_title"),
                    'lat' => $place_lat,
                    'lng' => $place_lng,
                ]);
                $feed->feed_place()->updateOrCreate([], ['place_id' => $place->id]);
            }

            DB::commit();
            return success(['result' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function show_feed(Request $request, $feed_id): array
    {
        $data = Feed::where(['id' => $feed_id, 'user_id' => token()->uid])->update(['is_hidden' => false]);

        return success(['result' => $data > 0]);
    }

    public function hide_feed(Request $request, $feed_id): array
    {
        $data = Feed::where(['id' => $feed_id, 'user_id' => token()->uid])->update(['is_hidden' => true]);

        return success(['result' => $data > 0]);
    }

    public function destroy($id): array
    {
        $user_id = token()->uid;

        $feed = Feed::where(['id' => $id, 'user_id' => $user_id])->first();

        if (isset($feed)) {
            $data = $feed->delete();
            return success(['result' => true]);
        } else {
            return success([
                'result' => false,
                'reason' => 'not my feed',
            ]);
        }
    }
}
