<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionContent
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property int $content_id
 * @method static \Illuminate\Database\Eloquent\Builder|MissionContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionContent query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionContent whereContentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionContent whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionContent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionContent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
