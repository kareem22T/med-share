<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FCM extends Model
{
    use HasFactory;
    protected $fillable = [
        "user_id",
        "token"
    ];

    public $timestamps = false;
    protected $table = "user_fcm_tokens";

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

}
