<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\FeedMission;
use App\Models\Mission;
use App\Models\MissionComment;
use App\Models\MissionStat;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BookmarkController extends Controller
{
    public function index(Request $request, $category_id = null, $limit = null): array
    {
        $user_id = token()->uid;

        $category_id = $category_id ?? $request->get('category_id');
        $limit = $limit ?? $request->get('limit', 0);

        $data = MissionStat::when($category_id, function ($query, $category_id) {
            $query->where('missions.mission_category_id', $category_id);
        })
            ->when($category_id === 0, function ($query) {
                $query->where('is_event', 1);
            })
            ->where('mission_stats.user_id', $user_id)
            ->join('missions', 'missions.id', 'mission_stats.mission_id')
            ->join('mission_categories', 'mission_categories.id', 'missions.mission_category_id')
            ->join('users', 'users.id', 'missions.user_id') // 미션 제작자
            ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
            ->leftJoin('products', 'products.id', 'mission_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
            ->select([
                'mission_categories.id as category_id', 'mission_categories.title as category_title', 'mission_categories.emoji',
                'missions.id', 'missions.title', DB::raw("IFNULL(missions.description, '') as description"),
                'missions.is_event',
                DB::raw("missions.id <= 1213 and missions.is_event = 1 as is_old_event"), challenge_type(),
                'missions.started_at', 'missions.ended_at',
                DB::raw("(missions.started_at is null or missions.started_at<=now()) and
                    (missions.ended_at is null or missions.ended_at>now()) as is_available"),
                'missions.thumbnail_image', 'missions.success_count',
                'mission_stat_id' => MissionStat::withTrashed()->select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'mission_stat_user_id' => MissionStat::withTrashed()->select('user_id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'mission_products.type as product_type', //'mission_products.product_id',
                DB::raw("IF(mission_products.type='inside', mission_products.product_id, mission_products.outside_product_id) as product_id"),
                DB::raw("IF(mission_products.type='inside', brands.name_ko, outside_products.brand) as product_brand"),
                DB::raw("IF(mission_products.type='inside', products.name_ko, outside_products.title) as product_title"),
                DB::raw("IF(mission_products.type='inside', products.thumbnail_image, outside_products.image) as product_image"),
                'outside_products.url as product_url',
                DB::raw("IF(mission_products.type='inside', products.price, outside_products.price) as product_price"),
                'place_address' => Place::select('address')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_title' => Place::select('title')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_description' => Place::select('description')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_image' => Place::select('image')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_url' => Place::select('url')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_id', 'missions.id'),
                'today_upload' => FeedMission::selectRaw("COUNT(1) > 0")
                    ->whereColumn('feed_missions.mission_id', 'missions.id')->where('feeds.user_id', $user_id)
                    ->where('feeds.created_at', '>=', init_today())
                    ->whereNull('feeds.deleted_at')
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id'),
                'bookmarks' => FeedMission::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id')
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id')
                    ->whereColumn('user_id', '!=', 'missions.user_id'),
                'comments' => MissionComment::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'),
                'has_check' => FeedMission::selectRaw("COUNT(1) > 0")
                    ->whereColumn('feed_missions.mission_id', 'missions.id')->where('feeds.user_id', $user_id)
                    ->where('feeds.created_at', '>=', init_today())
                    ->whereNull('feeds.deleted_at')
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id'),
                'feed_id' => FeedMission::select('feed_id')
                    ->whereColumn('feed_missions.mission_id', 'missions.id')->where('feeds.user_id', $user_id)
                    ->where('feeds.created_at', '>=', init_today())
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id')->limit(1),
            ])
            ->withCount(['feeds' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }])
            ->orderBy('has_check')
            ->orderBy('is_event')
            ->orderBy('event_order')
            ->orderBy('id', 'desc')
            ->when($limit, function ($query, $limit) {
                $query->take($limit);
            })->get();

        if (!$category_id) {
            $tmp = [];
            foreach ($data->groupBy('category_title') as $i => $item) {
                $tmp[] = [
                    'id' => $item[0]->category_id, 'title' => $i, 'emoji' => $item[0]->emoji,
                    'missions' => $item->toArray()
                ];
            }
            $data = $tmp;
        }

        return success([
            'result' => true,
            'missions' => $data,
        ]);
    }

    public function store(Request $request, $mission_id = null): array
    {
        $user_id = token()->uid;
        $mission_id = $mission_id ?? $request->get('mission_id');

        if (is_null($mission_id)) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        if (MissionStat::where(['user_id' => $user_id, 'mission_id' => $mission_id])->exists()) {
            return success(['result' => false, 'reason' => 'already bookmark']);
        /*} elseif (MissionStat::where('user_id', $user_id)->count() >= 5) {
            return success(['result' => false, 'reason' => 'bookmark is full']);*/
        } else {
            $data = MissionStat::create([
                'user_id' => $user_id,
                'mission_id' => $mission_id,
            ]);
            return success(['result' => (bool)$data]);
        }
    }

    public function destroy($id): array
    {
        $user_id = token()->uid;

        if (is_null($id)) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        if ($bookmark = MissionStat::where(['user_id' => $user_id, 'mission_id' => $id])->first()) {
            DB::beginTransaction();

            $data = $bookmark->delete();

            DB::commit();
            return success(['result' => $data > 0]);
        } else {
            return success([
                'result' => false,
                'reason' => 'not bookmark',
            ]);
        }
    }
}
