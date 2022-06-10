<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductReviewImage
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage query()
 * @mixin \Eloquent
 */
class ProductReviewImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
