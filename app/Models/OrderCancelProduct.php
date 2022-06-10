<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderCancelProduct
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCancelProduct query()
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
