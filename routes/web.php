<?php

use App\Models\DeleteUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function (Request $request) {
    return $request->ip();
});

Route::group(['prefix' => 'admin', 'middleware' => ['web', 'admin'], 'as' => 'admin.'], function () {
    Route::get('/', function (Request $request) {
        return $request->ip();
    });

    Route::get('/login', function () {
        return view('admin.login');
    })->name('login.index');
    Route::post('/login', function (Request $request) {
        $user = $request->only('email', 'password');

        if (Auth::attempt($user, true)) {
            return redirect()->route('admin.user');
        } else {
            echo "<script>alert('로그인에 실패했습니다.');</script>";
        }
    })->name('login');
    Route::get('/logout', function () {
        Auth::logout();
        return redirect(request()->headers->get('referer'));
    })->name('logout');

    Route::get('/user', function (Request $request) {
        $filter = $request->get('filter', 'day');

        $date = [
            'all' => User::withoutTrashed(),
            'day' => User::where('created_at', '>=', date('Y-m-d')),
            'week' => User::where('created_at', '>=', date('Y-m-d', time() - (86400 * date('w')))),
            'month' => User::where('created_at', '>=', date('Y-m')),
        ];
        $users_count = [
            'all' => $date['all']->count(),
            'day' => $date['day']->count(),
            'week' => $date['week']->count(),
            'month' => $date['month']->count(),
        ];
        $deleted_old_users_count = DeleteUser::whereNull('users.id')
            ->leftJoin('users', 'users.id', 'delete_users.user_id')
            ->distinct()
            ->count('user_id');
        $deleted_users_count = User::onlyTrashed()->count();

        $users = $date[$filter]->select([
            'users.*', 'area' => area(),
            'following' => \App\Models\Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id')
        ])
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('admin.user', [
            'users_count' => $users_count,
            'deleted_old_users_count' => $deleted_old_users_count,
            'deleted_users_count' => $deleted_users_count,
            'users' => $users,
        ]);
    })->name('user');
});
