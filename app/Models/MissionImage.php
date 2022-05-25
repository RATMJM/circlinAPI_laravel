<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionImage
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property int|null $order
 * @property string $type 이미지인지 비디오인지 (image / video)
 * @property string $image
 * @property string|null $thumbnail_image
 * @method static \Illuminate\Database\Eloquent\Builder|MissionImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionImage whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionImage whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionImage whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionImage whereThumbnailImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionImage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = ['mission_id'];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
