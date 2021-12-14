<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderExchangeProduct
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $order_product_id
 * @property int $qty
 * @property string $reason 교환 사유
 * @property string $status 상태 (request|receive|complete)
 * @property string|null $canceled_at 취소 접수 거절 일시
 * @property string|null $received_at 교환 접수 일시
 * @property string|null $completed_at 회수 완료 일시
 * @property int $redelivery_id
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct whereCanceledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct whereOrderProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct whereRedeliveryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderExchangeProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
