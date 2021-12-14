<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SortUser
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property int $order
 * @method static \Illuminate\Database\Eloquent\Builder|SortUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SortUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SortUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|SortUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SortUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SortUser whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SortUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SortUser whereUserId($value)
 * @mixin \Eloquent
 */
class SortUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
