<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\FeedComment
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $feed_id
 * @property int $user_id
 * @property int $group
 * @property int $depth
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Feed $feed
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment newQuery()
 * @method static \Illuminate\Database\Query\Builder|FeedComment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment whereDepth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedComment whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|FeedComment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|FeedComment withoutTrashed()
 * @mixin \Eloquent
 */
class FeedComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
