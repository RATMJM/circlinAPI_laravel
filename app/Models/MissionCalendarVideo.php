<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionCalendarVideo
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property string $day
 * @property string $url
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCalendarVideo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCalendarVideo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCalendarVideo query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCalendarVideo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCalendarVideo whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCalendarVideo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCalendarVideo whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCalendarVideo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCalendarVideo whereUrl($value)
 * @mixin \Eloquent
 */
class MissionCalendarVideo extends Model
{
    use HasFactory;
}
