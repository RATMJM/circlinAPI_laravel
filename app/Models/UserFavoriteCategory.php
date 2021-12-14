<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserFavoriteCategory
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property int $mission_category_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserFavoriteCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserFavoriteCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserFavoriteCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserFavoriteCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserFavoriteCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserFavoriteCategory whereMissionCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserFavoriteCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserFavoriteCategory whereUserId($value)
 * @mixin \Eloquent
 */
class UserFavoriteCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
