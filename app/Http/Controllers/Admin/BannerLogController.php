<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BannerLogController extends Controller
{
    public function index(Request $request)
    {
        $now = date('Y-m-d H:i:s');

        $floats = Banner::where('banners.type', 'float')
            ->select([
            'banners.id', 'banners.type', 'banners.name', 'banners.image', 'banners.started_at', 'banners.ended_at',
            DB::raw("(banners.started_at is null or banners.started_at<='$now') and
                    (banners.ended_at is null or banners.ended_at>'$now') as is_available"),
            'banners.link_type',
            DB::raw("CASE WHEN link_type in ('mission','event_mission') THEN mission_id
                    WHEN link_type='product' THEN product_id
                    WHEN link_type='notice' THEN notice_id END as link_id"), 'banners.link_url',
            'views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                ->where('banner_logs.type', 'view'),
            'android_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                ->where('banner_logs.type', 'view')->where('banner_logs.device_type', 'android'),
            'ios_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                ->where('banner_logs.type', 'view')->where('banner_logs.device_type', 'ios'),
            'etc_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                ->where('banner_logs.type', 'view')->where(function ($query) {
                    $query->whereNotIn('banner_logs.device_type', ['android', 'ios'])
                        ->orWhereNull('banner_logs.device_type');
                }),
            'clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                ->where('banner_logs.type', 'click'),
            'android_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                ->where('banner_logs.type', 'click')->where('banner_logs.device_type', 'android'),
            'ios_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                ->where('banner_logs.type', 'click')->where('banner_logs.device_type', 'ios'),
            'etc_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                ->where('banner_logs.type', 'click')->where(function ($query) {
                    $query->whereNotIn('banner_logs.device_type', ['android', 'ios'])
                        ->orWhereNull('banner_logs.device_type');
                }),
        ])
            ->groupBy('banners.id')
            ->orderBy('is_available', 'desc')
            ->orderBy('banners.sort_num', 'desc')
            ->orderBy('banners.id', 'desc')
            ->paginate(20);
        $locals = Banner::where('banners.type', 'like', "local%")
            ->select([
                'banners.id', 'banners.type', 'banners.name', 'banners.image', 'banners.started_at', 'banners.ended_at',
                DB::raw("(banners.started_at is null or banners.started_at<='$now') and
                    (banners.ended_at is null or banners.ended_at>'$now') as is_available"),
                'banners.link_type',
                DB::raw("CASE WHEN link_type in ('mission','event_mission') THEN mission_id
                    WHEN link_type='product' THEN product_id
                    WHEN link_type='notice' THEN notice_id END as link_id"), 'banners.link_url',
                'views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view'),
                'android_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view')->where('banner_logs.device_type', 'android'),
                'ios_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view')->where('banner_logs.device_type', 'ios'),
                'etc_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view')->where(function ($query) {
                    $query->whereNotIn('banner_logs.device_type', ['android', 'ios'])
                        ->orWhereNull('banner_logs.device_type');
                }),
                'clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click'),
                'android_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click')->where('banner_logs.device_type', 'android'),
                'ios_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click')->where('banner_logs.device_type', 'ios'),
                'etc_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click')->where(function ($query) {
                    $query->whereNotIn('banner_logs.device_type', ['android', 'ios'])
                        ->orWhereNull('banner_logs.device_type');
                }),
            ])
            ->groupBy('banners.id')
            ->orderBy('is_available', 'desc')
            ->orderBy('banners.sort_num', 'desc')
            ->orderBy('banners.id', 'desc')
            ->paginate(20);
        $shops = Banner::where('banners.type', 'shop')
            ->select([
                'banners.id', 'banners.type', 'banners.name', 'banners.image', 'banners.started_at', 'banners.ended_at',
                DB::raw("(banners.started_at is null or banners.started_at<='$now') and
                    (banners.ended_at is null or banners.ended_at>'$now') as is_available"),
                'banners.link_type',
                DB::raw("CASE WHEN link_type in ('mission','event_mission') THEN mission_id
                    WHEN link_type='product' THEN product_id
                    WHEN link_type='notice' THEN notice_id END as link_id"), 'banners.link_url',
                'views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view'),
                'android_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view')->where('banner_logs.device_type', 'android'),
                'ios_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view')->where('banner_logs.device_type', 'ios'),
                'etc_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view')->where(function ($query) {
                    $query->whereNotIn('banner_logs.device_type', ['android', 'ios'])
                        ->orWhereNull('banner_logs.device_type');
                }),
                'clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click'),
                'android_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click')->where('banner_logs.device_type', 'android'),
                'ios_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click')->where('banner_logs.device_type', 'ios'),
                'etc_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click')->where(function ($query) {
                    $query->whereNotIn('banner_logs.device_type', ['android', 'ios'])
                        ->orWhereNull('banner_logs.device_type');
                }),
            ])
            ->groupBy('banners.id')
            ->orderBy('is_available', 'desc')
            ->orderBy('banners.sort_num', 'desc')
            ->orderBy('banners.id', 'desc')
            ->paginate(20);

        return view('admin.banner.log.index', [
            'floats' => $floats,
            'locals' => $locals,
            'shops' => $shops,
        ]);
    }

    public function show(Request $request, $id)
    {
        $now = date('Y-m-d H:i:s');

        $banner = Banner::where('banners.id', $id)
            ->select([
                'banners.id', 'banners.type', 'banners.name', 'banners.image', 'banners.started_at', 'banners.ended_at',
                DB::raw("(banners.started_at is null or banners.started_at<='$now') and
                    (banners.ended_at is null or banners.ended_at>'$now') as is_available"),
                'banners.link_type',
                DB::raw("CASE WHEN link_type in ('mission','event_mission') THEN mission_id
                    WHEN link_type='product' THEN product_id
                    WHEN link_type='notice' THEN notice_id END as link_id"), 'banners.link_url',
                'views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view'),
                'android_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view')->where('banner_logs.device_type', 'android'),
                'ios_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view')->where('banner_logs.device_type', 'ios'),
                'etc_views_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'view')->where(function ($query) {
                    $query->whereNotIn('banner_logs.device_type', ['android', 'ios'])
                        ->orWhereNull('banner_logs.device_type');
                }),
                'clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click'),
                'android_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click')->where('banner_logs.device_type', 'android'),
                'ios_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click')->where('banner_logs.device_type', 'ios'),
                'etc_clicks_count' => BannerLog::selectRaw("COUNT(1)")->whereColumn('banner_id', 'banners.id')
                    ->where('banner_logs.type', 'click')->where(function ($query) {
                    $query->whereNotIn('banner_logs.device_type', ['android', 'ios'])
                        ->orWhereNull('banner_logs.device_type');
                }),
            ])
            ->groupBy('banners.id')
            ->orderBy('is_available', 'desc')
            ->orderBy('banners.sort_num', 'desc')
            ->orderBy('banners.id', 'desc')
            ->firstOrFail();

        $data = BannerLog::where('banner_id', $id)
            ->select(DB::raw("CAST(created_at as DATE) as `date`"))
            ->groupBy('date')
            ->orderBy('date', 'desc');
        $data = DB::table($data)->select([
            'date',
            'views_count' => BannerLog::selectRaw("COUNT(1)")->where('banner_logs.banner_id', $id)
                ->whereColumn(DB::raw("CAST(banner_logs.created_at as DATE)"), 'date')
                ->where('banner_logs.type', 'view'),
            'android_views_count' => BannerLog::selectRaw("COUNT(1)")->where('banner_logs.banner_id', $id)
                ->whereColumn(DB::raw("CAST(banner_logs.created_at as DATE)"), 'date')
                ->where('banner_logs.type', 'view')->where('banner_logs.device_type', 'android'),
            'ios_views_count' => BannerLog::selectRaw("COUNT(1)")->where('banner_logs.banner_id', $id)
                ->whereColumn(DB::raw("CAST(banner_logs.created_at as DATE)"), 'date')
                ->where('banner_logs.type', 'view')->where('banner_logs.device_type', 'ios'),
            'etc_views_count' => BannerLog::selectRaw("COUNT(1)")->where('banner_logs.banner_id', $id)
                ->whereColumn(DB::raw("CAST(banner_logs.created_at as DATE)"), 'date')
                ->where('banner_logs.type', 'view')->where(function ($query) {
                    $query->whereNotIn('banner_logs.device_type', ['android', 'ios'])
                        ->orWhereNull('banner_logs.device_type');
                }),
            'clicks_count' => BannerLog::selectRaw("COUNT(1)")->where('banner_logs.banner_id', $id)
                ->whereColumn(DB::raw("CAST(banner_logs.created_at as DATE)"), 'date')
                ->where('banner_logs.type', 'click'),
            'android_clicks_count' => BannerLog::selectRaw("COUNT(1)")->where('banner_logs.banner_id', $id)
                ->whereColumn(DB::raw("CAST(banner_logs.created_at as DATE)"), 'date')
                ->where('banner_logs.type', 'click')->where('banner_logs.device_type', 'android'),
            'ios_clicks_count' => BannerLog::selectRaw("COUNT(1)")->where('banner_logs.banner_id', $id)
                ->whereColumn(DB::raw("CAST(banner_logs.created_at as DATE)"), 'date')
                ->where('banner_logs.type', 'click')->where('banner_logs.device_type', 'ios'),
            'etc_clicks_count' => BannerLog::selectRaw("COUNT(1)")->where('banner_logs.banner_id', $id)
                ->whereColumn(DB::raw("CAST(banner_logs.created_at as DATE)"), 'date')
                ->where('banner_logs.type', 'click')->where(function ($query) {
                    $query->whereNotIn('banner_logs.device_type', ['android', 'ios'])
                        ->orWhereNull('banner_logs.device_type');
                }),
        ])
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('admin.banner.log.show', [
            'banner' => $banner,
            'data' => $data,
        ]);
    }
}
