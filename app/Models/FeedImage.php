<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FeedImage
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $feed_id
 * @property int|null $order
 * @property string $type 이미지인지 비디오인지 (image / video)
 * @property string $image 원본 이미지
 * @property string|null $thumbnail_image 미리 작게 보여줄 이미지
 * @method static \Illuminate\Database\Eloquent\Builder|FeedImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedImage whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedImage whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedImage whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedImage whereThumbnailImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedImage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FeedImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
