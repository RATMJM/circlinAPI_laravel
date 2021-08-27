<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_important' => 'bool',
    ];

    public function missions()
    {
        return $this->hasMany(Mission::class);
    }
}
