<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "buyer_id",
        "payment_methode",
        "status",
        "is_paid",
        "shipping_fees",
        "payment_fees",
        "sub_total",
        "total",
    ];

    public function buyer()
    {
        return $this->belongsTo('App\Models\User', 'buyer_id');
    }

    public function products()
    {
        return $this->hasMany('App\Models\Ordered_Product', 'order_id');
    }

}
