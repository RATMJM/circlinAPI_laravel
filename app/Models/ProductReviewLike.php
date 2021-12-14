<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductReviewLike
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $product_review_id
 * @property int $user_id
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike whereProductReviewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewLike whereUserId($value)
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
