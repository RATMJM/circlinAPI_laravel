<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $type = $request->get('type');
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

        $missions = match ($type) {
            'all' => $date[$filter]->where(function ($query) use ($keyword) {
                $query->where('users.nickname', 'like', "%$keyword%")
                    ->orWhere('users.email', 'like', "%$keyword%")
                    ->orWhere('missions.title', 'like', "%$keyword%");
            }),
            default => $date[$filter],
        };

        $missions = $missions->join('mission_categories', 'mission_categories.id', 'missions.mission_category_id')
            ->join('users', 'users.id', 'missions.user_id')
            ->leftJoin('mission_areas', 'mission_areas.mission_id', 'missions.id')
            // ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
            ->select([
                'mission_categories.title as category', 'missions.id',
                'missions.title', 'missions.description', 'missions.thumbnail_image', 'area' => area_like('mission_areas'),
                'missions.success_count', 'missions.created_at',
                'users.nickname', 'users.email', 'users.gender',
            ])
            ->orderBy('missions.id', 'desc')
            ->paginate(50);

        return view('admin.mission', [
            'missions_count' => $missions_count,
            'missions' => $missions,
            'filter' => $filter,
            'type' => $type,
            'keyword' => $keyword,
        ]);
    }
}
