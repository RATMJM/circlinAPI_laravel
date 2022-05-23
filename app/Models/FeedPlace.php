<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FeedPlace
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $feed_id
 * @property int $place_id
 * @property-read \App\Models\Feed $feed
 * @method static \Illuminate\Database\Eloquent\Builder|FeedPlace newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedPlace newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedPlace query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedPlace whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedPlace whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedPlace whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedPlace wherePlaceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedPlace whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FeedPlace extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }
}
