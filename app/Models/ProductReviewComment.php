<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\ProductReviewComment
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $product_review_id
 * @property int $user_id
 * @property int $group
 * @property int $depth
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment newQuery()
 * @method static \Illuminate\Database\Query\Builder|ProductReviewComment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment whereDepth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment whereProductReviewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductReviewComment whereUserId($value)
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
