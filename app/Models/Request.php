<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'byuer_id'
    ];

    public function requested_by()
    {
        return $this->belongsTo('App\Models\User', 'byuer_id');
    }

    public function products()
    {
        return $this->hasMany('App\Models\Request_product', 'request_id');
    }

}
