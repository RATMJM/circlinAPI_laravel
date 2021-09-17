<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeleteUser;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $type = $request->get('type');
        $keyword = $request->get('keyword');

        $date = [
            'all' => User::withoutTrashed(),
            'day' => User::where('created_at', '>=', date('Y-m-d')),
            'week' => User::where('created_at', '>=', date('Y-m-d', time() - (86400 * date('w')))),
            'month' => User::where('created_at', '>=', date('Y-m')),
        ];
        $users_count = [];
        foreach ($date as $i => $item) {
            $users_count[$i] = $item->count();
        }
        $deleted_old_users_count = DeleteUser::whereNull('users.id')
            ->leftJoin('users', 'users.id', 'delete_users.user_id')
            ->distinct()
            ->count('user_id');
        $deleted_users_count = User::onlyTrashed()->count();

        $users = match ($type) {
            'all' => $date[$filter]->where(function ($query) use ($keyword) {
                $query->where('nickname', 'like', "%$keyword%")
                    ->orWhere('email', 'like', "%$keyword%");
            }),
            default => $date[$filter],
        };

        $users = $users->join('user_stats', 'user_stats.user_id', 'users.id')
            ->select([
            'users.*', 'area' => area(), 'user_stats.birthday',
            'following' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
        ])
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('admin.user', [
            'users_count' => $users_count,
            'deleted_old_users_count' => $deleted_old_users_count,
            'deleted_users_count' => $deleted_users_count,
            'users' => $users,
            'filter' => $filter,
            'type' => $type,
            'keyword' => $keyword,
        ]);
    }
}
