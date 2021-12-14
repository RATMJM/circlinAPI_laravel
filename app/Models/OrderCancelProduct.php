<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderCancelProduct
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $order_product_id
 * @property int $qty
 * @property string $reason 취소 사유
 * @property string $status 상태 (request|complete)
 * @property string|null $canceled_at 취소 접수 거절 일시
 * @property string|null $completed_at 취소 완료 일시
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct whereCanceledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct whereOrderProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderCancelProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
