<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FeedProduct
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $feed_id
 * @property string $type 내부상품인지 외부상품인지 (inside|outside)
 * @property int|null $product_id
 * @property int|null $outside_product_id
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder|FeedProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedProduct whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedProduct whereOutsideProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedProduct whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FeedProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function product()
    {
        return $this->hasOne(Product::class);
    }
}
