<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\UserMission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookmarkController extends Controller
{
    public function index(Request $request, $limit = 0): array
    {
        $user_id = token()->uid;

        $category_id = $request->get('category_id');
        $limit = $request->get('limit', $limit);

        $data = Mission::select(['id', 'title', 'description'])
            ->when($category_id, function ($query) use ($category_id) {
                $query->where('mission_category_id', $category_id);
            })
            ->whereHas('user_mission', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->orderBy('id');

        if ($limit > 0) {
            $data->take($limit);
        }

        $data = $data->get();

        return success([
            'result' => true,
            'missions' => $data,
        ]);
    }

    public function create(): array
    {
        abort(404);
    }

    public function store(Request $request): array
    {
        $user_id = token()->uid;
        $mission_id = $request->get('mission_id');

        if (is_null($mission_id)) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        if (UserMission::where(['user_id' => $user_id, 'mission_id' => $mission_id])->exists()) {
            return success(['result' => false, 'reason' => 'already bookmark']);
        } else {
            $data = UserMission::create([
                'user_id' => $user_id,
                'mission_id' => $mission_id,
            ]);
            return success(['result' => (bool)$data]);
        }
    }

    public function show($id): array
    {
        abort(404);
    }

    public function edit($id): array
    {
        abort(404);
    }

    public function update(Request $request, $id): array
    {
        abort(404);
    }

    public function destroy($id): array
    {
        $user_id = token()->uid;

        if (is_null($id)) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        if ($bookmark = UserMission::where(['user_id' => $user_id, 'mission_id' => $id])->first()) {
            DB::beginTransaction();

            $data = $bookmark->delete();

            DB::commit();
            return success(['result' => $data > 0]);
        } else {
            return success([
                'result' => false,
                'reason' => 'not bookmark',
            ]);
        }
    }
}
