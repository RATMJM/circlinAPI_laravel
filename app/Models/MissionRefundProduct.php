<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionRefundProduct
 *
 * @property int $id
 * @property int $mission_id
 * @property int $product_id
 * @property int $limit 제품 최대 구매 수량
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $food_id
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRefundProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRefundProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRefundProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRefundProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRefundProduct whereFoodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRefundProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRefundProduct whereLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRefundProduct whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRefundProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRefundProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionRefundProduct extends Model
{
    use HasFactory;
}
