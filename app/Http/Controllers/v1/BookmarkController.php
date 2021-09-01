<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\FeedMission;
use App\Models\Mission;
use App\Models\MissionStat;
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

        $data = Mission::when($category_id, function ($query, $category_id) {
            $query->where('missions.mission_category_id', $category_id);
        })
            ->when($category_id === 0, function ($query) {
                $query->where('event_order', '>', 0);
            })
            ->whereHas('mission_stats', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->join('mission_categories', 'mission_categories.id', 'missions.mission_category_id')
            ->select([
                'mission_categories.id as category_id', 'mission_categories.title as category_title', 'mission_categories.emoji',
                'missions.id', 'missions.title', DB::raw("COALESCE(missions.description, '') as description"),
                DB::raw("missions.event_order > 0 as is_event"),
                DB::raw("missions.id <= 1213 and missions.event_order > 0 as is_old_event"), challenge_type(),
                'missions.started_at', 'missions.ended_at',
                'missions.thumbnail_image', 'missions.success_count',
                'mission_stat_id' => MissionStat::withTrashed()->select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'mission_stat_user_id' => MissionStat::withTrashed()->select('user_id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'has_check' => FeedMission::selectRaw("COUNT(1) > 0")
                    ->whereColumn('feed_missions.mission_id', 'missions.id')->where('feeds.user_id', $user_id)
                    ->where('feeds.created_at', '>=', date('Y-m-d', time()))
                    ->whereNull('feeds.deleted_at')
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id'),
                'feed_id' => FeedMission::select('feed_id')
                    ->whereColumn('feed_missions.mission_id', 'missions.id')->where('feeds.user_id', $user_id)
                    ->where('feeds.created_at', '>=', date('Y-m-d', time()))
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id')->limit(1),
            ])
            ->withCount(['feeds' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }])
            ->orderBy('has_check')
            ->orderBy(DB::raw("event_order=0"))
            ->orderBy('event_order')
            ->orderBy('id')
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
