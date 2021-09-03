<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Feed;
use App\Models\Follow;
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

        $limit = max($request->get('limit', 50), 1);

        $users = User::where('feeds.created_at', '>=', init_today(time()-(86400*7)))
            ->whereNotNull('users.nickname')
            ->whereNotIn('users.id', Follow::select('target_id')->whereColumn('user_id', 'users.id'))
            ->join('users', 'users.id', 'feeds.user_id')
            ->leftJoin('sort_users', 'sort_users.user_id', 'users.id')
            ->select([
                'users.id',
                'together_following' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id')
                    ->whereHas('user_target_follow', function ($query) use ($user_id) {
                        $query->where('user_id', $user_id);
                    }),
            ])
            ->orderBy(DB::raw('`order`+(together_following*200)'), 'desc')
            ->take($limit);

        $users = User::where('users.id', '!=', $user_id)
            ->where(Feed::select('created_at')->whereColumn('user_id', 'users.id')->orderBy('id', 'desc')->limit(1),
                '>=', init_today(time()-(86400*7)))
            ->whereNotNull('users.nickname')
            ->whereDoesntHave('followers', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->leftJoin('sort_users', 'sort_users.user_id', 'users.id')
            ->select([
                'users.id',
                'together_following' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id')
                    ->whereHas('user_target_follow', function ($query) use ($user_id) {
                        $query->where('user_id', $user_id);
                    }),
            ])
            ->orderBy(DB::raw('`order`+(together_following*200)'), 'desc')
            // ->orderBy('users.id', 'desc')
            ->take($limit);

        $users = User::joinSub($users, 'u', function ($query) {
            $query->on('u.id', 'users.id');
        })
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'together_following',
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
}
