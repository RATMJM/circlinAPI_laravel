<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $type = $request->get('type', 'all');
        $search_type = $request->get('search_type');
        $keyword = $request->get('keyword');

        $date = [
            'all' => Mission::withoutTrashed(),
            'day' => Mission::where('missions.created_at', '>=', date('Y-m-d')),
            'week' => Mission::where('missions.created_at', '>=', date('Y-m-d', time() - (86400 * date('w')))),
            'month' => Mission::where('missions.created_at', '>=', date('Y-m')),
        ];
        $missions_count = [];
        foreach ($date as $i => $item) {
            $missions_count[$i] = $item->count();
        }

        $missions = Mission::select([
            'mission_categories.title as category',
            'missions.id',
            'missions.title',
            'missions.description',
            'missions.thumbnail_image',
            //'area' => area_like('mission_areas'),
            'missions.is_event',
            'missions.success_count',
            'missions.created_at',
            'users.nickname',
            'users.email',
            'users.gender',
        ])
            ->join('mission_categories', 'mission_categories.id', 'missions.mission_category_id')
            ->join('users', 'users.id', 'missions.user_id')
            ->leftJoin('mission_areas', 'mission_areas.mission_id', 'missions.id')
            ->when($type, fn($query, $type) => match ($type) {
                'normal' => $query->where('missions.is_event', false),
                'event' => $query->where('missions.is_event', true),
                default => null,
            })
            ->when($filter, fn($query, $filter) => match ($filter) {
                'day' => $query->where('missions.created_at', '>=', now()->toDateString()),
                'week' => $query->where('missions.created_at', '>=', now()->subDays(now()->dayOfWeek)->toDateString()),
                'month' => $query->where('missions.created_at', '>=', now()->setDay(1)->toDateString()),
                default => null,
            })
            ->when($search_type, fn($query, $search_type) => match ($search_type) {
                'all' => $query->where(function ($query) use ($keyword) {
                    $query->where('users.nickname', 'like', "%$keyword%")
                        ->orWhere('users.email', 'like', "%$keyword%")
                        ->orWhere('missions.title', 'like', "%$keyword%");
                }),
                default => null,
            })
            ->with('mission_areas', fn($query) => $query->select([
                'mission_id',
                'name' => Area::select('name')
                    ->where('code', DB::raw("CONCAT(SUBSTRING(area_code,1,5),'00000')"))->take(1),
            ]))
            ->orderBy('missions.id', 'desc')
            ->paginate(50);

        return view('admin.mission.index', [
            'missions_count' => $missions_count,
            'missions' => $missions,
            'filter' => $filter,
            'keyword' => $keyword,
        ]);
    }

    public function show($id)
    {
        $data = Mission::select([
            'id',
            'title',
            'description',
            'thumbnail_image',
            'is_event',
        ])
            ->where('id', $id)
            ->firstOrFail();

        return view('admin.mission.show', [
            'data' => $data,
        ]);
    }
}
