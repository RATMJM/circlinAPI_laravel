<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Area;
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

        return Area::select()->where(DB::raw('CONCAT(name_lg, name_md, name_sm)'), 'like', "%$text%")
            ->take(10)->get()->toArray();
    }

    public function category(Request $request): array
    {
        return MissionCategory::all()->toArray();
    }

    public function suggest_user(Request $request): array
    {
        $limit = max(min($request->get('limit', 50), 50), 1);

        return User::select(['id', 'nickname', 'profile_image'])
            ->addSelect(['area' => Area::selectRaw('CONCAT_WS(\' \', name_lg, name_md, name_sm)')
                ->whereColumn('users.area_code', 'areas.ctg_sm')->limit(1)])
            ->inRandomOrder()->take($limit)->get()->toArray();
    }
}
