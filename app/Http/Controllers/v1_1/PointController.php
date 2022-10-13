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

            // if ($type === 'feed') {
            //     if ($table_like->withTrashed()->where(["{$type}_id" => $id, 'user_id' => $user_id])->doesntExist()
            //         && PointHistory::where(["{$type}_id" => $id, 'reason' => 'feed_check'])->sum('point') < 1000) {
            //         $res = PointController::change_point($target_id, $point += 10, 'feed_check', 'feed', $id);
            //         $paid_point = $res['success'] && $res['data']['result'];
            //
            //         // 지금이 10번째 피드체크 && 100회까지만 지급
            //         if ($count % 10 === 9 && $count < 100) {
            //             $res = PointController::change_point($user_id, 10, 'feed_check_reward');
            //             NotificationController::send($user_id, 'feed_check_reward', null, null, false,
            //                 ['point' => 10, 'point2' => 100 - ($count+1)]);
            //             $take_point = $res['success'] && $res['data']['result'];
            //         }
            //
            //         $count += 1;
            //     }
            // }

            // feed comment 이벤트
            $reasons_with_daily_receive_limit = ['feed_comment_reward', 'feed_comment_withdraw', 'feed_check, feed_check_reward'];
            if (in_array($reason, $reasons_with_daily_receive_limit) && $user_id=64477) {

                $daily_limit = 500;
                $current_point = PointHistory::where([
                    "user_id" => $user_id,
                    "reason" => $reasons_with_daily_receive_limit
                ])
                ->where('created_at', '>=', init_today())
                ->sum('point');

                if ($current_point < $daily_limit) {
                    // 일 획득 총합이 500 미만이라면 추가 지급 가능
                    if ($current_point + $point > $daily_limit) {
                        // 이번 요청의 $point 금액을 더한 일 획득 총합이 500 초과가 되면 안됨. 이번 요청의 $point 금액을 더한 결과가 500이 되도록 $point 액수를 조정
                        $point = $daily_limit - $current_point;
                    } else {
                        // Do nothing
                        false;
                    }
                } else {
                    // 일 획득 총합이 500 이상이므로 더 이상 포인트 지급 불가
                    return success(['result' => true]);
                }
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

            $inserted_point_history_id = PointHistory::create($data);

            DB::commit();

            return success(['result' => true, 'id' => $inserted_point_history_id]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }
}
