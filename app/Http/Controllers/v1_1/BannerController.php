<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\MissionStat;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BannerController extends Controller
{
    public function category_banner(Request $request, $category_id = null): array
    {
        return $this->index(['local', 'local.'.($category_id ?? $request->get('category_id'))]);
    }

    public function index($type)
    {
        $now = date('Y-m-d H:i:s');

        $banners = Banner::whereIn('type', Arr::wrap($type))
            /*->where(function ($query) use ($now) {
                $query->where('started_at', '<=', $now)
                    ->orWhereNull('started_at');
            })*/
            ->where(function ($query) use ($now) {
                $query->where('ended_at', '>', $now)
                    ->orWhereNull('ended_at');
            })
            ->leftJoin('common_codes', function ($query) {
                $query->on('common_codes.ctg_sm', 'banners.link_type')
                    ->where('common_codes.ctg_lg', 'click_action');
            })
            ->select([
                'banners.image', 'banners.link_type', 'common_codes.content_ko as link',
                DB::raw("CASE WHEN link_type in ('mission','event_mission') THEN mission_id
                    WHEN link_type='product' THEN product_id
                    WHEN link_type='notice' THEN notice_id END as link_id"), 'banners.link_url'
            ])
            ->orderBy('sort_num', 'desc')
            ->orderBy('banners.id', 'desc')
            ->get();


        foreach ($banners as $i => $banner) {
            $banners[$i]->link = code_replace($banner->link, ['id' => $banner->link_id]);
        }

        return success([
            'result' => true,
            'banners' => $banners,
        ]);
    }
}
