<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderReturnProduct
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderReturnProduct query()
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
