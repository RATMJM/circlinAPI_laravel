<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatUser;
use App\Models\Feed;
use App\Models\FeedComment;
use App\Models\FeedImage;
use App\Models\FeedLike;
use App\Models\FeedMission;
use App\Models\FeedPlace;
use App\Models\FeedProduct;
use App\Models\Mission;
use App\Models\MissionStat;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
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
        $missions = $request->get('missions');
        $is_hidden = $request->get('is_hidden', 0);

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

        if (!$content || !$files) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        try {
            DB::beginTransaction();

            $feed = Feed::create([
                'user_id' => $user_id,
                'content' => $content,
                'is_hidden' => $is_hidden == 1,
            ]);

            // 이미지 및 동영상 업로드
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
                        $uploaded_file = Storage::disk('ftp3')->put("/Image/SNS/$user_id", new File($tmp_path));
                        @unlink($tmp_path);
                    } elseif (str_starts_with($file->getMimeType(), 'video/')) {
                        $type = 'video';
                        $uploaded_file = Storage::disk('ftp3')->put("/Image/SNS/$user_id", $file);

                        $thumbnail = "Image/SNS/$user_id/thumb_" . $file->hashName();

                        $host = config('filesystems.disks.ftp3.host');
                        $username = config('filesystems.disks.ftp3.username');
                        $password = config('filesystems.disks.ftp3.password');
                        $url = image_url(3, $uploaded_file);
                        $url2 = image_url(3, $thumbnail);
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
                        'image' => image_url(3, $uploaded_file),
                        'thumbnail_image' => image_url(3, $uploaded_thumbnail ?: $uploaded_file),
                    ]);
                }
            }

            // 미션 적용
            if ($missions) {
                foreach ($missions as $mission_id) {
                    $stat = MissionStat::where(['user_id' => $user_id, 'mission_id' => $mission_id])
                        ->orderBy('id', 'desc')
                        ->firstOrCreate();

                    if (($mission = Mission::where('id', $mission_id)->first())?->success_count === 1 && FeedMission::where([
                            'feed_id' => $feed->id,
                            'mission_id' => $mission_id,
                        ])->doesntExist()) {
                        $completed_missions[] = $mission;
                    }

                    FeedMission::create([
                        'feed_id' => $feed->id,
                        'mission_stat_id' => $stat->id,
                        'mission_id' => $mission_id,
                    ]);
                }
            }

            if ($product_id) {
                FeedProduct::create([
                    'feed_id' => $feed->id,
                    'type' => 'inside',
                    'product_id' => $product_id,
                ]);
            } elseif ($product_brand && $product_title && $product_price && $product_url) {
                FeedProduct::create([
                    'feed_id' => $feed->id,
                    'type' => 'outside',
                    'image' => $product_image,
                    'brand' => $product_brand,
                    'title' => $product_title,
                    'price' => $product_price,
                    'url' => $product_url,
                ]);
            }

            if ($place_address && $place_title && $place_image) {
                FeedPlace::create([
                    'feed_id' => $feed->id,
                    'address' => $place_address,
                    'title' => $place_title,
                    'description' => $place_description,
                    'image' => $place_image,
                    'url' => $place_url ?? urlencode("https://google.com/search?q=$place_title"),
                ]);
            }

            /*$noti = Follow::where(['target_id' => $user_id, 'feed_notify' => true])
                ->join('users', 'users.id', 'follows.user_id')->pluck('device_token');

            foreach ($)*/

            DB::commit();

            return success([
                'result' => true,
                'feed' => $feed,
                'completed_missions' => $completed_missions ?? null,
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
            ->leftJoin('feed_places', 'feed_places.feed_id', 'feeds.id')
            ->select([
                'feeds.id', 'feeds.created_at', 'feeds.content', 'feeds.is_hidden',
                'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'feed_products.type as product_type', 'feed_products.product_id',
                DB::raw("IF(feed_products.type='inside', brands.name_ko, feed_products.brand) as product_brand"),
                DB::raw("IF(feed_products.type='inside', products.name_ko, feed_products.title) as product_title"),
                DB::raw("IF(feed_products.type='inside', products.thumbnail_image, feed_products.image) as product_image"),
                'feed_products.url as product_url',
                DB::raw("IF(feed_products.type='inside', products.price, feed_products.price) as product_price"),
                'feed_places.address as place_address', 'feed_places.title as place_title', 'feed_places.description as place_description',
                'feed_places.image as place_image', 'feed_places.url as place_url',
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
            ->first();

        if (is_null($feed)) {
            return success([
                'result' => false,
                'reason' => 'not found',
            ]);
        }

        $feed->product = arr_group($feed, ['type', 'id', 'brand', 'title', 'image', 'url', 'price'], 'product_');
        $feed->place = arr_group($feed, ['address', 'title', 'description', 'image', 'url'], 'place_');

        $feed->images = $feed->images()->select(['type', 'image'])->orderBy('order')->get();

        $feed->missions = $feed->missions()
            ->join('missions', 'missions.id', 'feed_missions.mission_id')
            ->join('mission_categories', 'mission_categories.id', 'missions.mission_category_id')
            ->select([
                'missions.id', 'mission_categories.emoji', 'missions.title',
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
            ])
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

            if ($product_id) {
                $feed->product()->update([
                    'type' => 'inside',
                    'product_id' => $product_id,
                ]);
            } elseif ($product_brand && $product_title && $product_price && $product_url) {
                $feed->product()->update([
                    'type' => 'outside',
                    'image' => $product_image,
                    'brand' => $product_brand,
                    'title' => $product_title,
                    'price' => $product_price,
                    'url' => $product_url,
                ]);
            }

            if ($place_address && $place_title && $place_image) {
                $feed->place()->update([
                    'address' => $place_address,
                    'title' => $place_title,
                    'description' => $place_description,
                    'image' => $place_image,
                    'url' => $place_url ?? urlencode("https://google.com/search?q=$place_title"),
                ]);
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

        $feed = Feed::where('id', $id)->first();

        if ($feed->user_id === $user_id) {
            $data = $feed->delete();

            DB::commit();
            return success(['result' => true]);
        } else {
            return success([
                'result' => false,
                'reason' => 'not my feed',
            ]);
        }
    }
}
