<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\ProductReviewComment
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment newQuery()
 * @method static \Illuminate\Database\Query\Builder|ProductReviewComment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment query()
 * @method static \Illuminate\Database\Query\Builder|ProductReviewComment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ProductReviewComment withoutTrashed()
 * @mixin \Eloquent
 */
class ProductReviewComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
