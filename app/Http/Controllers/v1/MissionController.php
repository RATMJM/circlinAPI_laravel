<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\MissionCategory;
use App\Models\UserMission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    public function category(Request $request): array
    {
        return success([
            'result' => true,
            'categories' => MissionCategory::select([
                    'mission_categories.id',
                    'mission_categories.emoji',
                    'mission_categories.title',
                ])
                ->get(),
        ]);
    }

    public function add_bookmark(Request $request): array
    {
        $user_id = token()->uid;
        $mission_id = $request->get('mission_id');

        if ($mission_id) {
            DB::beginTransaction();

            $data = UserMission::create([
                'user_id' => $user_id,
                'mission_id' => $mission_id,
            ]);

            DB::commit();
            return success(['result' => true]);
        } else {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }
    }

    public function remove_bookmark(Request $request): array
    {
        $user_id = token()->uid;
        $mission_id = $request->get('mission_id');

        if ($mission_id) {
            if ($mission = UserMission::where(['user_id' => $user_id, 'mission_id' => $mission_id])) {
                DB::beginTransaction();

                $data = $mission->delete();

                DB::commit();
                return success(['result' => true]);
            } else {
                return success([
                    'result' => false,
                    'reason' => 'not bookmark',
                ]);
            }
        } else {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }
    }
}
