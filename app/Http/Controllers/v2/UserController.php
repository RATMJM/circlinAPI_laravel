<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 나를 팔로우
     */
    public function follower(Request $request, $user_id = null): array
    {
        $uid = token()->uid;
        if (is_null($user_id)) $user_id = $uid;

        $keyword = $request->get('keyword');

        $page = $request->get('page', 0);
        $limit = $request->get('limit', 20);

        $users = Follow::select([
            'users.id',
            'users.nickname',
            'users.profile_image',
            'users.gender',
            'area' => area_like(),
            'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
            'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                ->where('user_id', $uid),
        ])
            ->join('users', 'users.id', 'follows.user_id')
            ->where('follows.target_id', $user_id)
            ->when($keyword, function ($query, $keyword) {
                $query->where('users.nickname', 'like', "%$keyword%");
            })
            ->orderBy('follows.id', 'desc');
        $users_count = $users->count();
        $users = $users->skip($page * $limit)->take($limit)->get();

        return success([
            'result' => true,
            'users' => $users,
            'users_count' => $users_count,
        ]);
    }

    /**
     * 내가 팔로우
     */
    public function following(Request $request, $user_id = null): array
    {
        $uid = token()->uid;
        if (is_null($user_id)) $user_id = $uid;

        $keyword = $request->get('keyword');

        $page = $request->get('page', 0);
        $limit = $request->get('limit', 20);

        $users = Follow::select([
            'users.id',
            'users.nickname',
            'users.profile_image',
            'users.gender',
            'area' => area_like(),
            'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
            'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                ->where('user_id', $uid),
        ])
            ->join('users', 'users.id', 'follows.target_id')
            ->where('follows.user_id', $user_id)
            ->when($keyword, function ($query, $keyword) {
                $query->where('users.nickname', 'like', "%$keyword%");
            })
            ->orderBy('follows.id', 'desc');
        $users_count = $users->count();
        $users = $users->skip($page * $limit)->take($limit)->get();

        return success([
            'result' => true,
            'users' => $users,
            'users_count' => $users_count,
        ]);
    }
}
