<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionCategory
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $mission_category_id
 * @property string $title
 * @property string|null $emoji 타이틀 앞의 이모지
 * @property string|null $description 카테고리 설명
 * @property-read \App\Models\UserFavoriteCategory|null $favorite_category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mission[] $missions
 * @property-read int|null $missions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $refundProducts
 * @property-read int|null $refund_products_count
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCategory whereEmoji($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCategory whereMissionCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCategory whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function favorite_category()
    {
        return $this->hasOne('App\Models\UserFavoriteCategory');
    }

    public function missions()
    {
        return $this->hasMany('App\Models\Mission');
    }

    public function refundProducts()
    {
        return $this->belongsToMany(Product::class, MissionRefundProduct::class,
            'mission_id', 'product_id', 'mission_id', 'id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, MissionProduct::class,
            'mission_id', 'product_id', 'mission_id', 'id');
    }
}
