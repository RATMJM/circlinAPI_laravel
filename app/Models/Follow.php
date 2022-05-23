<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Follow
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property int $target_id
 * @property int $feed_notify
 * @property-read \App\Models\User $target
 * @property-read Follow $target_user_follow
 * @property-read \App\Models\User $user
 * @property-read Follow $user_target_follow
 * @method static \Illuminate\Database\Eloquent\Builder|Follow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Follow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Follow query()
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereFeedNotify($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereUserId($value)
 * @mixin \Eloquent
 */
class Follow extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // target_id 가 팔로우하는 사람
    public function user_target_follow()
    {
        return $this->belongsTo(Follow::class, 'user_id', 'target_id');
    }

    // user_id 를 팔로우하는 사람
    public function target_user_follow()
    {
        return $this->belongsTo(Follow::class, 'target_id', 'user_id');
    }
}
