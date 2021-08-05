<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\MissionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionCategoryController extends Controller
{
    public function index(): array
    {
        return success([
            'result' => true,
            'categories' => MissionCategory::select([
                'mission_categories.id',
                'mission_categories.emoji',
                'mission_categories.title',
            ])
                ->whereNotNull('mission_category_id')
                ->get(),
        ]);
    }

    public function create(): array
    {
        abort(404);
    }

    public function store(Request $request): array
    {
        abort(404);
    }

    public function show(Request $request, $id = null, $limit = null, $page = null, $sort = null): array
    {
        $limit = $limit ?? $request->get('limit', 20);
        $page = $page ?? $request->get('page', 0);
        $sort = $sort ?? $request->get('sort', 'popular');

        if ($id) {
            $data = Mission::where('mission_category_id', $id)
                ->leftJoin('user_missions', 'user_missions.mission_id', 'missions.id')
                ->leftJoin('mission_comments', 'mission_comments.mission_id', 'missions.id')
                ->select(['missions.title', 'missions.description',
                    DB::raw('COUNT(distinct user_missions.id) as bookmarks'),
                    DB::raw('COUNT(distinct mission_comments.id) as comments')])
                ->groupBy('missions.id');

            if ($sort === 'popular') {
                $data->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
            } elseif ($sort === 'new') {
                $data->orderBy('missions.id', 'desc');
            } else {
                $data->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
            }

            $data = $data->skip($page * $limit)->take($limit)->get();

            return success([
                'result' => true,
                'missions' => $data,
            ]);
        } else {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }
    }

    public function edit($id): array
    {
        abort(404);
    }

    public function update(Request $request, $id): array
    {
        abort(404);
    }

    public function destroy($id): array
    {
        abort(404);
    }
}
