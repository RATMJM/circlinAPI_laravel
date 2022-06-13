<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MissionNotice
 *
 * @property int $id
 * @property int $mission_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $title
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MissionNoticeImage[] $images
 * @property-read int|null $images_count
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNotice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNotice newQuery()
 * @method static \Illuminate\Database\Query\Builder|MissionNotice onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNotice query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNotice whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNotice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNotice whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNotice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNotice whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNotice whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNotice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|MissionNotice withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MissionNotice withoutTrashed()
 * @mixin \Eloquent
 */
class MissionNotice extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function images()
    {
        return $this->hasMany(MissionNoticeImage::class);
    }
}
