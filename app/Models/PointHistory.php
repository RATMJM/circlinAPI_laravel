<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PointHistory
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property int $point 변경된 포인트
 * @property int|null $result 잔여 포인트
 * @property string $reason 지급차감 사유 (like,mission 등) 출력될 내용은 common_codes
 * @property int|null $feed_id
 * @property int|null $order_id
 * @property int|null $mission_id
 * @property int|null $product_review_id
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory wherePoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereProductReviewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereUserId($value)
 * @mixin \Eloquent
 */
class PointHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
