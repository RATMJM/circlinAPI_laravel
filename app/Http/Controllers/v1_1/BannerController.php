<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerLog;
use App\Models\MissionStat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BannerController extends Controller
{
    public function category_banner(Request $request, $category_id = null): array
    {
        return $this->index($request, ['local', 'local.'.($category_id ?? $request->get('category_id'))]);
    }

    public function index(Request $request, $type)
    {
        $user_id = token()->uid;

        $now = date('Y-m-d H:i:s');

        $banners = Banner::whereIn('type', Arr::wrap($type))
            ->where(function ($query) use ($now) {
                $query->where('started_at', '<=', $now)
                    ->orWhereNull('started_at');
            })
            ->where(function ($query) use ($now) {
                $query->where('ended_at', '>', $now)
                    ->orWhereNull('ended_at');
            })
            ->leftJoin('common_codes', function ($query) {
                $query->on('common_codes.ctg_sm', 'banners.link_type')
                    ->where('common_codes.ctg_lg', 'click_action');
            })
            ->select([
                'banners.id', 'banners.image', 'banners.link_type', 'common_codes.content_ko as link',
                DB::raw("CASE WHEN link_type in ('mission','event_mission') THEN mission_id
                    WHEN link_type='product' THEN product_id
                    WHEN link_type='notice' THEN notice_id END as link_id"), 'banners.link_url'
            ])
            ->orderBy('sort_num', 'desc')
            ->orderBy('banners.id', 'desc')
            ->get();

        foreach ($banners as $i => $banner) {
            $params = match ($banner->link_type) {
                'event_mission' => [
                    'id' => $banner->link_id,
                    'user_id' => token_option()?->uid,
                ],
                default => ['id' => $banner->link_id],
            };
            $banner->link = code_replace($banner->link, $params);

            $banner->logs()->create([
                'user_id' => $user_id,
                'ip' => $request->ip(),
                'type' => 'view',
            ]);
        }

        return success([
            'result' => true,
            'banners' => $banners,
        ]);
    }

    public function click(Request $request, $id)
    {
        $user_id = token()->uid;

        BannerLog::create([
            'user_id' => $user_id,
            'device_type' => User::where('id', $user_id)->value('device_type'),
            'ip' => $request->ip(),
            'type' => 'click',
            'banner_id' => $id,
        ]);

        return success(['result' => true]);
    }
}
