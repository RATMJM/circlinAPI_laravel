<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SuggestUser
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestUser query()
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
