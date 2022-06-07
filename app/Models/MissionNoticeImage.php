<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionNoticeImage
 *
 * @property int $id
 * @property int $mission_notice_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $order
 * @property string $type 이미지인지 비디오인지 (image / video)
 * @property string $image
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNoticeImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNoticeImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNoticeImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNoticeImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNoticeImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNoticeImage whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNoticeImage whereMissionNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNoticeImage whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNoticeImage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionNoticeImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionNoticeImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = ['mission_notice_id'];
}
