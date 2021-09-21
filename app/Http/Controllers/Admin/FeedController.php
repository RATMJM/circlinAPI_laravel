<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $type = $request->get('type');
        $keyword = $request->get('keyword');

        $date = [
            'all' => Feed::withoutTrashed(),
            'day' => Feed::where('feeds.created_at', '>=', date('Y-m-d')),
            'week' => Feed::where('feeds.created_at', '>=', date('Y-m-d', time() - (86400 * date('w')))),
            'month' => Feed::where('feeds.created_at', '>=', date('Y-m')),
        ];
        $feeds_count = [];
        foreach ($date as $i => $item) {
            $feeds_count[$i] = $item->count();
        }

        $feeds = match ($type) {
            'all' => $date[$filter]->where(function ($query) use ($keyword) {
                $query->where('users.nickname', 'like', "%$keyword%")
                    ->orWhere('users.email', 'like', "%$keyword%")
                    ->orWhereHas('missions', function ($query) use ($keyword) {
                        $query->where('missions.title', 'like', "%$keyword%");
                    });
            }),
            default => $date[$filter],
        };

        $feeds = $feeds->join('users', 'users.id', 'feeds.user_id')
            ->select([
                'feeds.id', 'feeds.content', 'feeds.created_at',
                'users.nickname', 'users.email', 'users.gender',
            ])
            ->with('images')
            ->with('missions', function ($query) {
                $query->join('mission_categories', 'mission_categories.id', 'missions.mission_category_id')
                    ->join('users', 'users.id', 'missions.user_id')
                    ->leftJoin('mission_areas', 'mission_areas.mission_id', 'missions.id')
                    ->leftJoin('areas', 'areas.code', DB::raw("CONCAT(mission_areas.area_code,'00000')"))
                    ->select(['mission_categories.emoji', 'mission_categories.title as category', 'missions.title']);
            })
            ->orderBy('feeds.id', 'desc')
            ->paginate(50);

        return view('admin.feed', [
            'feeds_count' => $feeds_count,
            'feeds' => $feeds,
            'filter' => $filter,
            'type' => $type,
            'keyword' => $keyword,
        ]);
    }
}
