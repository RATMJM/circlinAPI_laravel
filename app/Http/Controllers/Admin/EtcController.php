<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\FeedMission;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EtcController extends Controller
{
    public function world_vision2_hiking(Request $request)
    {
        $type = $request->get('type');
        $keyword = $request->get('keyword');

        $feeds = match ($type) {
            'all' => Feed::query()->where(function ($query) use ($keyword) {
                $query->where('users.nickname', 'like', "%$keyword%")
                    ->orWhere('users.email', 'like', "%$keyword%")
                    ->orWhereHas('missions', function ($query) use ($keyword) {
                        $query->where('missions.title', 'like', "%$keyword%");
                    });
            }),
            default => Feed::query(),
        };

        $feeds = Feed::query()->where('feed_missions.mission_id', 1701)
            ->when($type, function ($query, $type) use ($keyword) {
                match ($type) {
                    'all' => $query->where(function ($query) use ($keyword) {
                        $query->where('users.nickname', 'like', "%$keyword%")
                            ->orWhere('users.email', 'like', "%$keyword%")
                            ->orWhere('places.title', 'like', "%$keyword%");
                    }),
                    default => null,
                };
            })
            ->join('users', 'users.id', 'feeds.user_id')
            ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id')
            ->leftJoin('feed_places', 'feed_places.feed_id', 'feeds.id')
            ->leftJoin('places', 'places.id', 'feed_places.place_id')
            ->select([
                'feeds.id', 'feeds.content', 'feeds.created_at', 'feeds.is_hidden',
                'users.nickname', 'users.email', 'users.gender',
                'places.title', 'places.address'
            ])
            ->with('images')
            ->orderBy('feeds.id', 'desc')
            ->paginate(50);

        $data = Feed::query()->where('feed_missions.mission_id', 1701)
            ->join('users', 'users.id', 'feeds.user_id')
            ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id')
            ->leftJoin('feed_places', 'feed_places.feed_id', 'feeds.id')
            ->leftJoin('places', 'places.id', 'feed_places.place_id')
            ->select([
                'places.title', DB::raw("COUNT(distinct feeds.id) as feeds_count")
            ])
            ->groupBy('places.id')
            ->orderBy('feeds_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.etc.world-vision2-hiking', [
            'feeds' => $feeds,
            'data' => $data,
            'type' => $type,
            'keyword' => $keyword,
        ]);
    }
}
