<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Mission;
use App\Models\Notice;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class BannerController extends Controller
{
    /**
     * 배너 목록
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'float');

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

    /**
     * 배너 정렬 수정
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function editAll(Request $request)
    {
        $type = $request->get('type', 'float');

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

    /**
     * 배너 정렬 수정
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|string
     * @throws \Throwable
     */
    public function updateAll(Request $request)
    {
        $type = $request->get('type', 'float');
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

    public function create()
    {
        $missions = Mission::select(['id', 'user_id', 'title', 'started_at', 'ended_at'])
            ->where('is_event', true)
            // ->where('ended_at', '>=', now())
            ->with('owner', fn($query) => $query->select('id', 'nickname'))
            ->orderBy('id', 'desc')
            ->get();

        $notices = Notice::select(['id', 'title', 'created_at'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.banner.create', [
            'missions' => $missions,
            'notices' => $notices,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:float,local,shop'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'started_at' => ['date'],
            'ended_at' => ['nullable', 'date'],
            'link_type' => ['required', 'in:mission,event_mission,notice,url'],
            'mission_id' => ['required_if:link_type,mission', 'exists:missions,id'],
            'notice_id' => ['required_if:link_type,notice', 'exists:notices,id'],
            'link_url' => ['required_if:link_type,url', 'nullable', 'url'],
            'image' => ['required', 'image'],
        ]);

        $file = $data['image'];

        $image = Image::make($file->getPathname());

        $image->orientate();

        $tmp_path = "{$file->getPath()}/banner_" . Str::uuid() . ".{$file->extension()}";
        $image->save($tmp_path);
        $uploaded_file = Storage::disk('s3')->put("/banner", new File($tmp_path));
        $data['image'] = image_url($uploaded_file);
        @unlink($tmp_path);

        $data['sort_num'] = Banner::where('type', $data['type'])->max('sort_num') + 1;

        Banner::create($data);

        return redirect()->route('admin.banner.index', ['type' => $data['type']]);
    }

    /**
     * 배너 조회
     *
     * @param $type
     * @param $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show(Request $request, $id)
    {
        $type = $request->get('type', 'float');

        $data = Banner::select(selectBanner())
            ->where('id', $id)
            ->with(['mission' => fn($q) => $q->withTrashed(), 'feed', 'product', 'notice'])
            ->firstOrFail();

        return view('admin.banner.show', ['data' => $data, 'type' => $type]);
    }

    /**
     * 배너 수정
     *
     * @param Request $request
     * @param $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Request $request, $id)
    {
        $type = $request->get('type', 'float');

        $data = Banner::select(selectBanner())
            ->where('id', $id)
            ->with(['mission' => fn($q) => $q->withTrashed(), 'feed', 'product', 'notice'])
            ->firstOrFail();

        $missions = Mission::select(['id', 'user_id', 'title', 'started_at', 'ended_at'])
            ->where('is_event', true)
            // ->where('ended_at', '>=', now())
            ->with('owner', fn($query) => $query->select('id', 'nickname'))
            ->orderBy('id', 'desc')
            ->get();

        $notices = Notice::select(['id', 'title', 'created_at'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.banner.edit', [
            'data' => $data,
            'missions' => $missions,
            'notices' => $notices,
            'type' => $type,
        ]);
    }

    /**
     * 배너 수정
     *
     * @param Request $request
     * @param $type
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'started_at' => ['date'],
            'ended_at' => ['nullable', 'date'],
            'link_type' => ['required', 'in:mission,event_mission,notice,url'],
            'mission_id' => ['required_if:link_type,mission', 'exists:missions,id'],
            'notice_id' => ['required_if:link_type,notice', 'exists:notices,id'],
            'link_url' => ['required_if:link_type,url', 'nullable', 'url'],
        ]);

        Banner::where('id', $id)->update($data);

        return redirect()->route('admin.banner.show', ['id' => $id]);
    }

    /**
     * 배너 삭제
     *
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $data = Banner::where('id', $id)->first();

        $type = $data?->type;
        $data?->delete();

        return redirect()->route('admin.banner.index', ['type' => $type]);
    }
}
