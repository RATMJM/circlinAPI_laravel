<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductReviewLike
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike query()
 * @mixin \Eloquent
 */
class ProductReviewLike extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
