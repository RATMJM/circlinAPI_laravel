<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\FeedLike
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $feed_id
 * @property int $user_id
 * @property int $point 대상에게 포인트 지급 여부
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|FeedLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedLike newQuery()
 * @method static \Illuminate\Database\Query\Builder|FeedLike onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedLike query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedLike whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedLike whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedLike wherePoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedLike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedLike whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|FeedLike withTrashed()
 * @method static \Illuminate\Database\Query\Builder|FeedLike withoutTrashed()
 * @mixin \Eloquent
 */
class FeedLike extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
