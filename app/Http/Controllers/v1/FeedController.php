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

        if (!$request->has('content') || !$request->hasFile('files')) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

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
                foreach ($missions as $mission) {
                    $stat = MissionStat::where(['user_id' => $user_id, 'mission_id' => $mission])
                        ->orderBy('id', 'desc')
                        ->firstOr(function () use ($user_id, $mission) {
                            return MissionStat::create([
                                'user_id' => $user_id,
                                'mission_id' => $mission,
                            ]);
                        });
                    FeedMission::create([
                        'feed_id' => $feed->id,
                        'mission_stat_id' => $stat->id,
                        'mission_id' => $mission,
                    ]);
                }

            }

            if ($product_id) {
                FeedProduct::create([
                    'feed_id' => $feed->id,
                    'type' => 'inside',
                    'product_id' => $product_id
                ]);
            } elseif ($product_brand && $product_title && $product_price && $product_url) {
                FeedProduct::create([
                    'feed_id' => $feed->id,
                    'type' => 'outside',
                    'image_url' => $product_image,
                    'brand_title' => $product_brand,
                    'product_title' => $product_title,
                    'price' => $product_price,
                    'product_url' => $product_url,
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

            DB::commit();

            return success([
                'result' => true,
                'feed' => $feed,
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
            ->join('user_stats', 'user_stats.user_id', 'users.id')
            ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
            ->leftJoin('feed_products', 'feed_products.feed_id', 'feeds.id')
            ->leftJoin('products', 'products.id', 'feed_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('feed_places', 'feed_places.feed_id', 'feeds.id')
            ->select([
                'feeds.id', 'feeds.created_at', 'feeds.content',
                'users.id as user_id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                'feed_products.type', 'feed_products.product_id',
                DB::raw("IF(feed_products.type='inside', brands.name_ko, feed_products.brand_title) as product_brand"),
                DB::raw("IF(feed_products.type='inside', products.name_ko, feed_products.product_title) as product_title"),
                DB::raw("IF(feed_products.type='inside', products.thumbnail_image, feed_products.image_url) as product_image"),
                'feed_products.product_url',
                DB::raw("IF(feed_products.type='inside', products.price, feed_products.price) as product_price"),
                'feed_places.address as place_address', 'feed_places.title as place_title', 'feed_places.description as place_description',
                'feed_places.image as place_image', 'feed_places.url as place_url',
                'like_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
            ])
            ->groupBy('feeds.id', 'users.id', 'user_stats.id', 'areas.id', 'feed_products.id', 'products.id',
                'brands.id', 'feed_places.id')
            ->first();

        $feed->product = arr_group($feed, ['id', 'brand', 'title', 'image', 'url', 'price'], 'product_');
        $feed->place = arr_group($feed, ['address', 'title', 'description', 'image', 'url'], 'place_');

        if (is_null($feed)) {
            return success([
                'result' => false,
                'reason' => 'not found feed',
            ]);
        }

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

        $comments = (new CommentController())->index('feed', $feed->id)['data']['comments'];

        return success([
            'result' => true,
            'feed' => $feed,
            'comments' => $comments,
        ]);
    }

    public function destroy($id): array
    {
        // $user_id =
    }
}
