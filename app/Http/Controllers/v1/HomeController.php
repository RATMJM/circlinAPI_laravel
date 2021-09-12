<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatUser;
use App\Models\Feed;
use App\Models\FeedComment;
use App\Models\FeedImage;
use App\Models\FeedLike;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionProduct;
use App\Models\MissionStat;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function town(Request $request, $category_id = null): array
    {
        $user_id = token()->uid;

        $category_id = $category_id ?? $request->get('category_id');

        $category_id = Arr::wrap($category_id ??
            Arr::pluck(($categories = (new MissionCategoryController())->index('town')['data']['categories']), 'id'));

        $local = $request->get('local');

        $tabs = [];
        foreach ($category_id as $id) {
            if ($id > 0) {
                $places = Place::when($id, function ($query, $id) {
                    $query->where('missions.mission_category_id', $id);
                })
                    ->when($local, function ($query) use ($user_id) {
                        $query->where(User::select('area_code')->where('id', $user_id), 'like', DB::raw("CONCAT(mission_areas.area_code,'%')"));
                    })
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->join('missions', function ($query) {
                        $query->on('missions.id', 'mission_places.mission_id')->whereNull('missions.deleted_at');
                    })
                    ->leftJoin('mission_areas', 'mission_areas.mission_id', 'missions.id')
                    ->select([
                        'places.id', 'places.address', 'places.title', 'places.description',
                        'places.image', 'places.url',
                        DB::raw("COUNT(distinct missions.id) as missions_count"),
                    ])
                    ->groupBy('places.id')
                    ->orderBy('missions_count', 'desc')
                    ->orderBy(DB::raw("MAX(missions.id)"), 'desc')
                    // ->take(2)
                    ->get();

                $products = MissionProduct::when($id, function ($query, $id) {
                    $query->where('missions.mission_category_id', $id);
                })
                    ->when($local, function ($query) use ($user_id) {
                        $query->where(User::select('area_code')->where('id', $user_id), 'like', DB::raw("CONCAT(mission_areas.area_code,'%')"));
                    })
                    ->join('missions', 'missions.id', 'mission_products.mission_id')
                    ->leftJoin('mission_areas', 'mission_areas.mission_id', 'missions.id')
                    ->leftJoin('products', 'products.id', 'mission_products.product_id')
                    ->leftJoin('brands', 'brands.id', 'products.brand_id')
                    ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
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
                    ->groupBy('mission_products.id')
                    ->orderBy('missions_count', 'desc')
                    ->orderBy(DB::raw("MAX(missions.id)"), 'desc')
                    // ->take(2)
                    ->get();
            }

            // $tmp = $id === 0 ? $category_id : $id;
            $missions = (new MissionCategoryController())->mission($request, $id, 3)['data'];
            $tabs[$id] = [
                'bookmark' => (new BookmarkController())->index($request, $id, 3)['data']['missions'],
                'banners' => (new BannerController())->category_banner($request, $id),
                'places' => $places ?? null,
                'products' => $products ?? null,
                'mission_total' => $missions['missions_count'],
                'missions' => $missions['missions'],
            ];
            break; // 첫번째 탭만 가져오도록
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
        $limit = $request->get('limit', 20);

        $data = Follow::where('follows.user_id', $user_id)
            ->where('feeds.is_hidden', false)
            ->where('feeds.created_at', '>=', date('Y-m-d H:i:s', time()-86400))
            ->join('feeds', function ($query) {
                $query->on('feeds.user_id', 'follows.target_id')
                    ->whereNull('feeds.deleted_at');
            })
            ->select('feeds.id')
            ->orderBy('feeds.id', 'desc');
        // $data = $data//->where(FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')->where('user_id', token()->uid), false)
        //     ->skip($page * $limit)->take($limit);

        $data = Feed::joinSub($data, 'f', function ($query) {
            $query->on('f.id', 'feeds.id');
        })
            ->join('users', 'users.id', 'feeds.user_id')
            ->leftJoin('feed_products', 'feed_products.feed_id', 'feeds.id')
            ->leftJoin('products', 'products.id', 'feed_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'feed_products.outside_product_id')
            ->select([
                'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area_like(),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
                'feeds.id as feed_id', 'feeds.created_at', 'feeds.content',
                'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('id')->limit(1),
                'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('id')->limit(1),
                'feed_products.type as product_type', 'feed_products.product_id',
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
            ->orderBy('has_check')
            ->orderBy('feeds.id', 'desc');
        $feeds_count = $data->count();
        $data = $data->skip($page * $limit)->take($limit)
            ->get();

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
                'missions.is_event',
                DB::raw("missions.id <= 1213 and missions.is_event = 1 as is_old_event"), challenge_type(),
                'missions.started_at', 'missions.ended_at',
                'missions.thumbnail_image', 'missions.success_count',
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id),
                'mission_stat_id' => MissionStat::withTrashed()->select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'mission_stat_user_id' => MissionStat::withTrashed()->select('user_id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                DB::raw("$user_id as mission_stat_user_id"),
            ])
            ->withCount(['feeds' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }])
            ->get();

        foreach ($missions->groupBy('feed_id') as $i => $mission) {
            $data[array_search($i, $feed_id->toArray())]->missions = $mission;
        }

        return success([
            'result' => true,
            'feeds_count' => $feeds_count,
            'feeds' => $data,
        ]);
    }

    public function badge(): array
    {
        $user_id = token()->uid;

        return success([
            'result' => true,
            'feeds' => 0,
            'missions' => MissionStat::where('user_id', $user_id)
                ->whereDoesntHave('feed_missions', function ($query) {
                    $query->where('created_at', '>=', init_today());
                })->count(),
            'notifies' => (new Collection((new NotificationController())->index()['data']['notifies']))
                ->where('is_read', false)->count(),
            'messages' => (new ChatController())->index(request())['data']['rooms']->sum('unread_total'),
        ]);
    }
}
