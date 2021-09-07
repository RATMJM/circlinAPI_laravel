<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function index(Request $request, $type): array
    {
        $area_code = substr($request->get('area_code'), 0, 5);

        $keywords = Keyword::where('type', $type)
            ->where(function ($query) use ($area_code) {
                $query->where('area_code', $area_code)
                    ->orWhereNull('area_code');
            })
            ->orderBy('id', 'desc')
            ->pluck('keyword');

        return success([
            'result' => true,
            'keywords' => $keywords,
        ]);
    }
}
