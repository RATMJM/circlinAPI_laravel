<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Follow;
use App\Models\MissionCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{
    public function area(Request $request): array
    {
        $text = $request->get('searchText');
        $text = mb_ereg_replace('/\s/', '', $text);

        $areas = Area::select(['ctg_sm as ctg',
            DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as name")])
            ->where(DB::raw('CONCAT(name_lg, name_md, name_sm)'), 'like', "%$text%")
            ->take(10)->get();

        return success([
            'result' => true,
            'areas' => $areas,
        ]);
    }

    public function suggest_user(Request $request): array
    {
        $user_id = token()->uid;

        $limit = max(min($request->get('limit', 50), 50), 1);

        return success([
            'result' => true,
            'users' => User::where('users.id', '!=', $user_id)
                ->whereNotNull('users.nickname')
                ->whereDoesntHave('followers', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                })
                ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
                ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
                ->leftJoin('follows', 'follows.target_id', 'users.id')
                ->select([
                    'users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                    DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                    DB::raw("COUNT(distinct follows.id) as follower"),
                    'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('follows.target_id', 'users.id')
                        ->where('follows.user_id', $user_id),
                ])
                ->groupBy(['users.id', 'user_stats.id', 'areas.id'])
                ->inRandomOrder()->take($limit)->get(),
        ]);
    }
}
