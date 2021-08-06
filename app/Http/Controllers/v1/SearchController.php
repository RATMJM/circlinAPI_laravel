<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\MissionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request): array
    {
        $user_id = token()->uid;

        $users = (new BaseController())->suggest_user($request)['data']['users'];

        $categories = MissionCategory::whereNotNull('mission_category_id')
            ->select(['id', DB::raw("COALESCE(emoji, '') as emoji"), 'title', DB::raw("COALESCE(description, '') as description")])
            ->orderBy('id')->get();

        return success([
            'result' => true,
            'users' => $users,
            'categories' => $categories,
        ]);
    }
}
