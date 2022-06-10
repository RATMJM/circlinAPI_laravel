<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderExchangeProduct
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderExchangeProduct query()
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
