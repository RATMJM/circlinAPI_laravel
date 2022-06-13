<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Feed;
use App\Models\Follow;
use App\Models\Version;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{
    public function area(Request $request): array
    {
        $text = $request->get('searchText');
        $text = mb_ereg_replace('/\s/', '', $text);

        $areas = Area::select(['code as ctg', 'name'])
            ->where('name', 'like', "%$text%")
            ->where(DB::raw("code % 100000"), '>', 0)
            ->distinct()
            ->orderBy('code')
            ->take(10)->get();

        return success([
            'result' => true,
            'areas' => $areas,
        ]);
    }

    public function suggest_user(Request $request): array
    {
        $user_id = token()->uid;

        $limit = max($request->get('limit', 30), 1);

        $users = Feed::where('feeds.created_at', '>=', init_today(time() - (86400 * 7)))
            ->whereDoesntHave('followers', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->where('feeds.user_id', '!=', $user_id)
            ->leftJoin('sort_users', 'sort_users.user_id', 'feeds.user_id')
            ->select([
                'sort_users.user_id',
                /*'together_following' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'sort_users.user_id')
                    ->whereHas('user_target_follow', function ($query) use ($user_id) {
                        $query->where('user_id', $user_id);
                    }),*/
            ])
            ->groupBy('sort_users.id')
            ->orderBy(DB::raw("`order` + " .
                // 같은 구
                "IF((select SUBSTRING(area_code,1,5) from users where id=$user_id)=
                    (select SUBSTRING(area_code,1,5) from users where id=sort_users.user_id),300,0) + " .
                // 다른 성별
                "IF((select gender from users where id=$user_id)!=(select gender from users where id=sort_users.user_id),300,0)"), 'desc')
            ->take($limit);

        $users = User::joinSub($users, 'u', function ($query) {
            $query->on('users.id', 'u.user_id');
        })
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area_like(),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                // 'together_following',
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('follows.user_id', $user_id),
            ])
            ->take($limit)->get();

        return success([
            'result' => true,
            'users' => $users,
        ]);
    }

    public function place(Request $request): array
    {
        $data = Place::where('title', $request->get('title'))
            ->where('is_important', true)
            ->orderBy('id')
            ->first();

        return success([
            'result' => isset($data),
            'title' => $data,
        ]);
    }

    public function latest_version(Request $request): array
    {
        $version = $request->get('version');

        $is_force = Version::where('id', '>', Version::select('id')
                ->where('version', $version)->value('id') ?? 0)
                ->where('is_force', true)->exists();

        return success([
            'result' => true,
            'is_force' => $is_force,
        ]);
    }
}
