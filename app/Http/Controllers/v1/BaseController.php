<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Follow;
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

        DB::enableQueryLog();

        $users = User::where('users.id', '!=', $user_id)
            ->whereNotNull('users.nickname')
            ->whereDoesntHave('followers', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->select([
                'users.id', 'users.nickname', 'users.profile_image',
                'gender' => UserStat::select('gender')->whereColumn('user_stats.user_id', 'users.id')->limit(1),
                'area' => Area::selectRaw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm))")
                    ->whereColumn('areas.ctg_sm', 'users.area_code')->limit(1),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('follows.target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('follows.target_id', 'users.id')
                    ->where('follows.user_id', $user_id),
            ])
            ->orderBy('id')
            ->take($limit)->get();

        dd(DB::getQueryLog());

        return success([
            'result' => true,
            'users' => $users,
        ]);
    }
}
