<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request_product extends Model
{
    use HasFactory;
    protected $fillable = [
        "id",
        "request_id",
        "product_id",
        "price_in_order",
        "ordered_quantity",
    ];

    protected $table = "request_products";

    public $timestamps = false;

    // Rellations
    public function request()
    {
        return $this->belongsTo('App\Models\Request', 'request_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }

}
