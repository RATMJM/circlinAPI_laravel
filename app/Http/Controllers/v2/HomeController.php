<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatUser;
use App\Models\Feed;
use App\Models\FeedComment;
use App\Models\FeedImage;
use App\Models\FeedLike;
use App\Models\Follow;
use App\Models\MissionStat;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function newsfeed(Request $request): array
    {
        $user_id = token()->uid;

        $page = $request->get('page', 0);
        $limit = $request->get('limit', 10);

        $data = Feed::select([
            'feeds.id',
            'users.id as user_id',
            'users.nickname',
            'users.profile_image',
            'users.gender',
            'area' => area_like(),
            'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
            'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                ->where('user_id', $user_id),
            'feeds.id as feed_id',
            'feeds.created_at',
            'feeds.content',
            'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
            'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('id')->limit(1),
            'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('id')->limit(1),
            'feed_products.type as product_type',
            'feed_products.product_id',
            DB::raw("IF(feed_products.type='inside', brands.name_ko, outside_products.brand) as product_brand"),
            DB::raw("IF(feed_products.type='inside', products.name_ko, outside_products.title) as product_title"),
            DB::raw("IF(feed_products.type='inside', products.thumbnail_image, outside_products.image) as product_image"),
            'outside_products.url as product_url',
            DB::raw("IF(feed_products.type='inside', products.price, outside_products.price) as product_price"),
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
            'has_paid_check' => FeedLike::withTrashed()->selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                ->where('user_id', token()->uid), // 해당 피드에 체크를 남겼던적 있는가
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
            ->join('users', function ($query) {
                $query->on('users.id', 'feeds.user_id')->whereNull('users.deleted_at');
            })
            ->join('follows', function ($query) use ($user_id) {
                $query->on('follows.target_id', 'users.id')->where('follows.user_id', $user_id);
            })
            ->leftJoin('feed_products', 'feed_products.feed_id', 'feeds.id')
            ->leftJoin('products', 'products.id', 'feed_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'feed_products.outside_product_id')
            ->where('feeds.is_hidden', false)
            ->where('feeds.created_at', '>=', date('Y-m-d H:i:s', time() - 86400))
            ->with('missions', function ($query) use ($user_id) {
                $query->select([
                    'feed_missions.feed_id',
                    'missions.id',
                    'missions.title',
                    'mission_categories.emoji',
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
                    'mission_stat_id' => MissionStat::withTrashed()
                        ->select('id')
                        ->whereColumn('mission_id', 'missions.id')
                        ->where('user_id', $user_id)
                        ->orderBy('id', 'desc')
                        ->limit(1),
                    'mission_stat_user_id' => MissionStat::withTrashed()
                        ->select('user_id')
                        ->whereColumn('mission_id', 'missions.id')
                        ->where('user_id', $user_id)
                        ->orderBy('id', 'desc')
                        ->limit(1),
                    DB::raw("$user_id as mission_stat_user_id"),
                ])
                    ->join('mission_categories', 'mission_categories.id', 'missions.mission_category_id')
                    ->withCount([
                        'feeds' => function ($query) use ($user_id) {
                            $query->where('user_id', $user_id);
                        },
                    ]);
            })
            ->orderBy('has_check')
            ->orderBy('feeds.id', 'desc')
            ->skip($page * $limit)
            ->take($limit)
            ->get();

        foreach ($data as $i => $item) {
            $data[$i]->product = arr_group($data[$i], [
                'type',
                'id',
                'brand',
                'title',
                'image',
                'url',
                'price',
            ], 'product_');
            $data[$i]->place = arr_group($data[$i], ['address', 'title', 'description', 'image', 'url'], 'place_');
        }

        return $data->toArray();
    }
}
