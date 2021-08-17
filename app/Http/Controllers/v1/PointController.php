<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\PointHistory;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    /**
     * 포인트 지급 / 차감 ($point 양수, 음수에 따라)
     */
    public static function change_point($user_id, $point, $reason): array
    {
        try {
            DB::beginTransaction();

            $user = User::where('id', $user_id)->first();

            if (is_null($user)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            PointHistory::create([
                'user_id' => $user_id,
                'point' => $point,
                'reason' => $reason,
            ]);

            $user->increment('point', $point);

            DB::commit();

            return success(['result' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }
}
