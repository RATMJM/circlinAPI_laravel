<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Place
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $address
 * @property string $title
 * @property string|null $description
 * @property string|null $image
 * @property float|null $lat 위도
 * @property float|null $lng 경도
 * @property string|null $url
 * @property bool $is_important
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Feed[] $feeds
 * @property-read int|null $feeds_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mission[] $missions
 * @property-read int|null $missions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Place newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Place newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Place query()
 * @method static \Illuminate\Database\Eloquent\Builder|Place whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Place whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Place whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Place whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Place whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Place whereIsImportant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Place whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Place whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Place whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Place whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Place whereUrl($value)
 * @mixin \Eloquent
 */
class Place extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_important' => 'bool',
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function missions()
    {
        return $this->belongsToMany(Mission::class, MissionPlace::class);
    }

    public function feeds()
    {
        return $this->belongsToMany(Feed::class, FeedPlace::class);
    }
}
