<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionProduct
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property string $type 내부상품인지 외부상품인지 (inside|outside)
 * @property int|null $product_id
 * @property int|null $outside_product_id
 * @method static \Illuminate\Database\Eloquent\Builder|MissionProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionProduct whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionProduct whereOutsideProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionProduct whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
