<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;
    protected $fillable = [
        "path",
        "product_id"
    ];

    public $table = "products_gallery";

    public $timestamps = false;

    //Relations
    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
}
