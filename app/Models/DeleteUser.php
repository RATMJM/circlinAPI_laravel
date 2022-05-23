<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\DeleteUser
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id 기존에는 user 삭제해서 fk 못검
 * @property string|null $reason 탈퇴사유
 * @method static \Illuminate\Database\Eloquent\Builder|DeleteUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeleteUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeleteUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|DeleteUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeleteUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeleteUser whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeleteUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeleteUser whereUserId($value)
 * @mixin \Eloquent
 */
class DeleteUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
