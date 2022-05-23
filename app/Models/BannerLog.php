<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BannerLog
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property string|null $device_type
 * @property string $ip
 * @property string $type view/click
 * @property int $banner_id
 * @method static \Illuminate\Database\Eloquent\Builder|BannerLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BannerLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BannerLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|BannerLog whereBannerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BannerLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BannerLog whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BannerLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BannerLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BannerLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BannerLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BannerLog whereUserId($value)
 * @mixin \Eloquent
 */
class BannerLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
