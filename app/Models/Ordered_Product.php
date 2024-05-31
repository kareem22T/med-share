<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ordered_Product extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "order_id",
        "product_id",
        "price_in_order",
        "ordered_quantity",
    ];

    protected $table = "order_products";

    public $timestamps = false;

    // Rellations
    public function order()
    {
        return $this->belongsTo('App\Models\Order', 'order_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }


}
