<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OutsideProduct
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $product_id 상품 고유 ID
 * @property string|null $brand
 * @property string $title
 * @property string|null $image
 * @property string $url
 * @property int $price
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mission[] $missions
 * @property-read int|null $missions_count
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OutsideProduct whereUrl($value)
 * @mixin \Eloquent
 */
class OutsideProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function missions()
    {
        return $this->belongsToMany(Mission::class, MissionProduct::class);
    }
}
