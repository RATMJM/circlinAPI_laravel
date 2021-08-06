<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): array
    {
        $user_id = token()->uid;

        $users = (new BaseController())->suggest_user($request)['data']['users'];

        $categories = (new MissionCategoryController())->index()['data']['categories'];

        return success([
            'result' => true,
            'users' => $users,
            'categories' => $categories,
        ]);
    }
}
