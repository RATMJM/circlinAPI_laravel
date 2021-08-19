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
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\MissionStat;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function town(Request $request, $category_id = null): array
    {
        $category_id = $category_id ?? $request->get('category_id');

        $category_id = Arr::wrap($category_id ??
            Arr::pluck(($categories = (new MissionCategoryController())->index('town')['data']['categories']), 'id'));

        $tabs = [];
        foreach ($category_id as $id) {
            $tmp = $id === 0 ? $category_id : $id;
            $tabs[$id] = [
                'bookmark' => (new BookmarkController())->index($request, $id, 3)['data']['missions'],
                'banners' => (new BannerController())->category_banner($id),
                'mission_total' => Mission::where(function ($query) {
                    $query->whereNull('ended_at')->orWhere('ended_at', '>', date('Y-m-d H:i:s'));
                })
                ->when($id, function ($query, $id) {
                    $query->whereIn('mission_category_id', Arr::wrap($id));
                })->count(),
                'missions' => (new MissionCategoryController())->mission($request, $tmp, 3)['data']['missions'],
            ];
            break;
        }

        if (count($category_id) > 1) {
            return success(['result' => true, 'categories' => $categories ?? [], 'tabs' => $tabs]);
        } else {
            return success(['result' => true, 'tabs' => $tabs[$category_id[0]]]);
        }
    }

    public function newsfeed(Request $request): array
    {
        $user_id = token()->uid;

        $page = $request->get('page', 0);
        $limit = $request->get('limit', 5);

        $data = Feed::where('is_hidden', false)
            ->whereHas('user', function ($query) use ($user_id) {
                $query->whereHas('followers', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                });
            })
            ->join('users', 'users.id', 'feeds.user_id')
            ->leftJoin('feed_products', 'feed_products.feed_id', 'feeds.id')
            ->leftJoin('products', 'products.id', 'feed_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('feed_places', 'feed_places.feed_id', 'feeds.id')
            ->select([
                'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
                'feeds.id as feed_id', 'feeds.created_at', 'feeds.content',
                'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('id')->limit(1),
                'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('id')->limit(1),
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
            ->orderBy('feeds.id', 'desc')
            ->skip($page * $limit)->take($limit)->get();

        foreach ($data as $i => $item) {
            $data[$i]->product = arr_group($data[$i], ['type', 'id', 'brand', 'title', 'image', 'url', 'price'], 'product_');
            $data[$i]->place = arr_group($data[$i], ['address', 'title', 'description', 'image', 'url'], 'place_');
        }

        $feed_id = $data->pluck('feed_id');

        $missions = Mission::whereIn('feed_missions.feed_id', $feed_id)
            ->join('feed_missions', 'feed_missions.mission_id', 'missions.id')
            ->join('mission_categories', 'mission_categories.id', 'missions.mission_category_id')
            ->select([
                'feed_missions.feed_id', 'missions.id', 'missions.title', 'mission_categories.emoji',
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('user_id', $user_id)
                    ->whereColumn('mission_id', 'missions.id'),
            ])
            ->get();

        foreach ($missions->groupBy('feed_id') as $i => $mission) {
            $data[array_search($i, $feed_id->toArray())]->missions = $mission;
        }

        return success([
            'result' => true,
            'feeds' => $data,
        ]);
    }

    public function badge(): array
    {
        $user_id = token()->uid;

        return success([
            'result' => true,
            'feeds' => random_int(0, 50),
            'missions' => MissionStat::where('user_id', $user_id)
                ->whereDoesntHave('feed_missions', function ($query) {
                    $query->where('created_at', '>=', date('Y-m-d', time()));
                })->count(),
            'notifies' => random_int(0, 50),
            'messages' => random_int(0, 200),
        ]);
    }
}
