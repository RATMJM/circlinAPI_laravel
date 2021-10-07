<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function missions()
    {
        return $this->belongsToMany(Mission::class, MissionProduct::class)
            ->where('mission_products.type', 'inside');
    }
}
