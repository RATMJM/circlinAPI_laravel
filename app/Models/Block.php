<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Block
 *
 * @property int|null $user_id 차단 요청자
 * @property int|null $target_id user_id에 해당하는 유저가 차단하고자하는 상대 유저
 * @method static \Illuminate\Database\Eloquent\Builder|Block newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Block newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Block query()
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereUserId($value)
 * @mixin \Eloquent
 */
class Block extends Model
{
    use HasFactory;

    protected $guarded = [];
}
