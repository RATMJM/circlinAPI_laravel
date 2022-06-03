<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Models\PointHistory;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class PointController extends Controller
{
    /**
     * @param Request $request
     *
     * @return array
     * @throws Throwable
     */
    public function store(Request $request): array
    {
        $user_id = token()->uid;

        $data = $request->validate([
            'point' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
            'type' => ['in:feed,order,mission,product_review,food_rating'],
            'id' => ['numeric'],
        ]);

        return self::change_point($user_id, $data['point'], $data['reason'], $data['type'] ?? null, $data['id'] ?? null);
    }

    /**
     * 포인트 지급 / 차감 ($point 양수, 음수에 따라)
     *
     * @param int $user_id
     * @param int $point
     * @param string $reason
     * @param string|null $type
     * @param int|null $id
     *
     * @return array
     * @throws Throwable
     */
    public static function change_point(int $user_id, int $point, string $reason, string $type = null, int $id = null): array
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

            $user->increment('point', $point);

            $data = [
                'user_id' => $user_id,
                'point' => $point,
                'result' => $user->point,
                'reason' => $reason,
            ];

            if (isset($type)) {
                $data = Arr::collapse([$data, ["{$type}_id" => $id]]);
            }

            PointHistory::create($data);

            DB::commit();

            return success(['result' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }
}
