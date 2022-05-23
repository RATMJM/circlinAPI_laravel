<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductReviewImage
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $product_review_id
 * @property int|null $order
 * @property string $type (image / video)
 * @property string $image
 * @property string|null $thumbnail_image
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage whereProductReviewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage whereThumbnailImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewImage whereUpdatedAt($value)
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
