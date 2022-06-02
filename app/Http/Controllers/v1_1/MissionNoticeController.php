<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Models\MissionNotice;
use Illuminate\Http\Request;

class MissionNoticeController extends Controller
{
    /**
     * GET /mission/{mission_id}/notice
     *
     * @param Request $request
     * @param $mission_id
     *
     * @return array
     */
    public function index(Request $request, $mission_id): array
    {
        $page = $request->get('page', 0);
        $limit = $request->get('limit', 10);

        $data = MissionNotice::select(['id', 'title', 'body', 'created_at'])
            ->where('mission_id', $mission_id)
            ->orderBy('id', 'desc');
        $count = $data->count();
        $data = $data->skip($page * $limit)->take($limit)->get();

        return success([
            'count' => $count,
            'data' => $data,
        ]);
    }

    /**
     * GET /mission/{mission_id}/notice/{id}
     *
     * @param $mission_id
     * @param $id
     *
     * @return array
     */
    public function show($mission_id, $id): array
    {
        $data = MissionNotice::select(['id', 'title', 'body', 'created_at'])
            ->where('id', $id)
            ->where('mission_id', $mission_id)
            ->with('images', fn($query) => $query->select(['mission_notice_id', 'type', 'image']))
            ->firstOrFail();

        return success($data);
    }

    /**
     * GET /mission/{mission_id}/notice/recent
     *
     * @param $mission_id
     *
     * @return array
     */
    public function recent($mission_id): array
    {
        return success(MissionNotice::where('mission_id', $mission_id)->max('id'));
    }
}
