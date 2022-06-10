<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $order_no
 * @property int $user_id
 * @property int $total_price 결제금액
 * @property int $use_point 사용한 포인트
 * @property \Illuminate\Support\Carbon|null $deleted_at 주문 취소 시점(refund에 관한 테이블, 컬럼이 없어 현재는 이것이 refund 역할을 함)
 * 주문 취소 시 주문 취소 커맨드를 입력하고(노션 '써클인 인수인계' 참조), 아임포트 어드민에서도 취소해줘야 한다.
 * @property string|null $imp_id 결제 식별번호(아임포트 키)
 * @property string|null $merchant_id
 * @property-read \App\Models\OrderDestination|null $destination
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderProduct[] $order_products
 * @property-read int|null $order_products_count
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Query\Builder|Order onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereImpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUsePoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Order withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Order withoutTrashed()
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function destination()
    {
        return $this->hasOne(OrderDestination::class);
    }

    public function order_products()
    {
        return $this->hasMany(OrderProduct::class);
    }
}
