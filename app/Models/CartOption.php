<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CartOption
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $cart_id
 * @property int $product_option_id
 * @property int $price
 * @method static \Illuminate\Database\Eloquent\Builder|CartOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartOption query()
 * @method static \Illuminate\Database\Eloquent\Builder|CartOption whereCartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartOption wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartOption whereProductOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartOption whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CartOption extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
