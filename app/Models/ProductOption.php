<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductOption
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $product_id
 * @property int $group
 * @property string $name_ko
 * @property string|null $name_en
 * @property int $price
 * @property string $status 현재 상태 (sale / soldout)
 * @property int $stock 재고
 * @property string|null $deleted_at
 * @property string|null $temp 현재 상태 (sale / soldout)
 * @property string|null $tempprice
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereNameKo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereTempprice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductOption extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
