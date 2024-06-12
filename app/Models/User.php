<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // If you're using Laravel Sanctum

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'isApproved',
        'email',
        'phone',
        'password',
        'picture',
        'signature',
        'pharmacy_name',
        'is_phone_verified',
        'is_email_verified',
        'phone_last_verfication_code',
        'email_last_verfication_code',
        'phone_last_verfication_code_expird_at',
        'email_last_verfication_code_expird_at',
        'lat',
        'lng',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // relations ships
    public function cart()
    {
        return $this->hasMany('App\Models\Cart', 'user_id');
    }

    public function fcm_token()
    {
        return $this->hasMany('App\Models\FCM', 'user_id');
    }

    public function wishlist()
    {
        return $this->hasMany('App\Models\Wishlist', 'user_id');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order', 'buyer_id');
    }

    public function requests()
    {
        return $this->hasMany('App\Models\Request', 'user_id');
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product', 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction', 'user_id');
    }

    public function nearstPharmacies($limit = 10, $maxDistance = 10)
    {
        $haversine = "(6371 * acos(cos(radians($this->lat)) * cos(radians(lat)) * cos(radians(lng) - radians($this->lng)) + sin(radians($this->lat)) * sin(radians(lat))))";

        return User::select('*')
            ->where("id", "!=", $this->id)
            ->selectRaw("{$haversine} AS distance")
            ->whereRaw("{$haversine} <= ?", [$maxDistance])
            ->orderBy('distance', 'asc')
            ->take($limit)
            ->get();
    }


}
