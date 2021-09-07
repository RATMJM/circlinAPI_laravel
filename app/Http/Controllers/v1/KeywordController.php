<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use App\Models\User;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function index(Request $request, $type): array
    {
        $user_id = token()->uid;
        $area_code = substr(User::where('id', $user_id)->value('area_code'), 0, 5);

        $keywords = Keyword::where('type', $type)
            ->where(function ($query) use ($area_code) {
                $query->where('area_code', 'like', "$area_code%")
                    ->orWhereNull('area_code');
            })
            ->orderBy('order')
            ->orderBy('id', 'desc')
            ->pluck('keyword');

        return success([
            'result' => true,
            'keywords' => $keywords,
        ]);
    }
}
