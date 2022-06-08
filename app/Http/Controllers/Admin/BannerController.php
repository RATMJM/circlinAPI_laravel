<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BannerController extends Controller
{
    public function index(Request $request, $type)
    {
        $data = Banner::select(selectBanner())
            ->where('type', $type)
            ->with(['mission' => fn($q) => $q->withTrashed(), 'feed', 'product', 'notice'])
            ->orderBy('is_available', 'desc')
            ->orderBy('sort_num', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('admin.banner.index', [
            'data' => $data,
            'type' => $type,
        ]);
    }

    public function editAll(Request $request, $type)
    {
        $data = Banner::select(selectBanner())
            ->where('type', $type)
            ->with(['mission' => fn($q) => $q->withTrashed(), 'feed', 'product', 'notice'])
            ->having('is_available', true)
            ->orderBy('sort_num', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.banner.edit-all', [
            'data' => $data,
            'type' => $type,
        ]);
    }

    public function updateAll(Request $request, $type)
    {
        $sort_num = collect($request->get('sort_num'));

        $data = Banner::where('type', $type)
            ->where(fn($query) => $query->where('started_at', '<=', now())->orWhereNull('started_at'))
            ->where(fn($query) => $query->where('ended_at', '>=', now())->orWhereNull('ended_at'))
            ->whereNull('deleted_at')
            ->orderBy('sort_num', 'desc')
            ->pluck('sort_num', 'id');

        // return [$data->sortBy('id')->pluck('id'), $sort_num->sortKeys()->keys()];
        if (count(array_diff($data->keys()->toArray(), $sort_num->keys()->toArray())) > 0) {
            $referer = $request->server('HTTP_REFERER');
            echo "<script>alert('데이터에 문제가 있습니다 다시 시도해주세요.');location.replace('$referer')</script>";
            return '';
        }

        $data = Banner::where('type', $type)
            ->where(fn($query) => $query->where('started_at', '<=', now())->orWhereNull('started_at'))
            ->where(fn($query) => $query->where('ended_at', '>=', now())->orWhereNull('ended_at'))
            ->whereNull('deleted_at')
            ->orderBy('sort_num', 'desc')
            ->get();

        DB::transaction(function () use ($data, $sort_num) {
            foreach ($data as $item) {
                $item->sort_num = $sort_num[$item->id];
                $item->save();
            }
        });

        return redirect()->route('admin.banner.index', ['type' => $type]);
    }

    public function show($type, $id)
    {
        $data = Banner::select(selectBanner())
            ->where('id', $id)
            ->with(['mission' => fn($q) => $q->withTrashed(), 'feed', 'product', 'notice'])
            ->firstOrFail();

        return view('admin.banner.show', ['data' => $data, 'type' => $type]);
    }

    public function edit($type, $id)
    {

    }

    public function update(Request $request, $type, $id)
    {

    }

    public function destroy($type, $id)
    {

    }
}
