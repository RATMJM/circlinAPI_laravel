<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NoticeMission
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $notice_id
 * @property int $mission_id
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeMission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeMission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeMission query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeMission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeMission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeMission whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeMission whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeMission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NoticeMission extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
