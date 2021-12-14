<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Feed
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property string $content
 * @property bool $is_hidden 비밀글 여부
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property float|null $distance 달린 거리
 * @property int|null $laptime 달린 시간
 * @property float|null $distance_origin 인식된 달린 거리
 * @property int|null $laptime_origin 인식된 달린 시간
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeedComment[] $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeedMission[] $feed_missions
 * @property-read int|null $feed_missions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeedPlace[] $feed_place
 * @property-read int|null $feed_place_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Follow[] $followers
 * @property-read int|null $followers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Follow[] $followings
 * @property-read int|null $followings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeedImage[] $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeedLike[] $likes
 * @property-read int|null $likes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mission[] $missions
 * @property-read int|null $missions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Place[] $place
 * @property-read int|null $place_count
 * @property-read \App\Models\FeedProduct|null $product
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Feed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feed newQuery()
 * @method static \Illuminate\Database\Query\Builder|Feed onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Feed query()
 * @method static \Illuminate\Database\Eloquent\Builder|Feed whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed whereDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed whereDistanceOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed whereLaptime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed whereLaptimeOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Feed withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Feed withoutTrashed()
 * @mixin \Eloquent
 */
class Feed extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_hidden' => 'bool',
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(FeedImage::class);
    }

    public function feed_missions()
    {
        return $this->hasMany(FeedMission::class);
    }

    public function missions()
    {
        return $this->belongsToMany(Mission::class, FeedMission::class);
    }

    public function likes()
    {
        return $this->hasMany(FeedLike::class);
    }

    public function users()
    {
        return $this->likes();
    }

    public function comments()
    {
        return $this->hasMany(FeedComment::class);
    }

    public function product()
    {
        return $this->hasOne(FeedProduct::class);
    }

    public function feed_place()
    {
        return $this->hasMany(FeedPlace::class);
    }

    public function place()
    {
        return $this->hasManyThrough(Place::class, FeedPlace::class);
    }

    public function followings()
    {
        return $this->hasMany(Follow::class, 'user_id', 'user_id');
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'target_id', 'user_id');
    }
}
