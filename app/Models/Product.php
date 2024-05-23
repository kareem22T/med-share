<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'price',
        'discount',
        'quantity',
        'expired_at',
    ];

    public function gallery()
    {
        return $this->hasMany('App\Models\Gallery', 'product_id');
    }

    public function postedBy()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
