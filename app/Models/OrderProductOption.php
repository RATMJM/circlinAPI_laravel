<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderProductOption
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $order_product_id
 * @property int $product_option_id
 * @property int $price
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProductOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProductOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProductOption query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProductOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProductOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProductOption whereOrderProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProductOption wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProductOption whereProductOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderProductOption whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderProductOption extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
