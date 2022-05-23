<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserWallpaper
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property string|null $title 스킨 이름
 * @property string $image
 * @property string|null $thumbnail_image
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallpaper newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallpaper newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallpaper query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallpaper whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallpaper whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallpaper whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallpaper whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallpaper whereThumbnailImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallpaper whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallpaper whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallpaper whereUserId($value)
 * @mixin \Eloquent
 */
class UserWallpaper extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
