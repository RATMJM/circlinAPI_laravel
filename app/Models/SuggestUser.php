<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SuggestUser
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property int $suggest_user_id
 * @property int $order
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser whereSuggestUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser whereUserId($value)
 * @mixin \Eloquent
 */
class SuggestUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
