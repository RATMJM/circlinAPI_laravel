<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\MissionCategory;
use App\Models\User;
use App\Models\UserStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{
    public function area(Request $request): array
    {
        $text = $request->get('searchText');
        $text = mb_ereg_replace('/\s/', '', $text);

        return Area::select()->where(DB::raw('CONCAT(name_lg, name_md, name_sm)'), 'like', "%$text%")
            ->take(10)->get()->toArray();
    }

    public function category(Request $request): array
    {
        return MissionCategory::all()->toArray();
    }

    public function suggest_user(Request $request): array
    {
        $user_id = token()->uid;

        $limit = max(min($request->get('limit', 50), 50), 1);

        return success([
            'result' => true,
            'users' => User::select(['users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                DB::raw("CONCAT_WS(' ', areas.name_lg, areas.name_md, areas.name_sm) as area"),
                DB::raw("COUNT(distinct follows.id) as follower")])
                ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
                ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
                ->leftJoin('follows', 'follows.target_id', 'users.id')
                ->where('users.id', '!=', $user_id)
                ->whereDoesntHave('followers', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                })
                ->groupBy(['users.id', 'user_stats.id', 'areas.id'])
                ->inRandomOrder()->take($limit)->get(),
        ]);
    }
}
