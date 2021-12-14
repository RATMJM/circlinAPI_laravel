<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserStat
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property string|null $birthday 생년월일
 * @property float|null $height
 * @property float|null $weight
 * @property float|null $bmi
 * @property int $yesterday_feeds_count 어제 체크해야했던 피드 수
 * @method static \Database\Factories\UserStatFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat whereBmi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserStat whereYesterdayFeedsCount($value)
 * @mixin \Eloquent
 */
class UserStat extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
