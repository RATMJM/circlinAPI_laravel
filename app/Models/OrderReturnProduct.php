<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderReturnProduct
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $order_product_id
 * @property int $qty
 * @property string $reason 반품 사유
 * @property string $status 상태 (request|receive|complete)
 * @property string|null $canceled_at 반품 접수 거절 일시
 * @property string|null $received_at 반품 회수 일시
 * @property string|null $completed_at 반품 완료 일시
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct whereCanceledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct whereOrderProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderReturnProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
