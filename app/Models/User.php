<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'file',
        'email',
        'password',
        'gender',
        'birthdate',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'gender' => Gender::class,
        'birthdate' => 'date:Y-m-d',
    ];

    public function getAgeAttribute()
    {
        if (!$this->birthdate) {
            return null;
        }
        return Carbon::parse($this->birthdate)->age;
    }

    public function getProfileHashAttribute()
    {
        return hash('sha256', json_encode([
            'gender' => $this->gender,
            'age' => $this->age,
        ]));
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function outfits()
    {
        return $this->hasMany(Outfit::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function following()
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'following_id',
            'followed_id'
        );
    }

    public function followers()
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'followed_id',
            'following_id',
        );
    }

    public function receivesBroadcastNotificationsOn()
    {
        return 'user-notifications.' . $this->id;
    }

    public function likesReceived()
    {
        return $this->hasManyThrough(
            Like::class,
            Outfit::class,
            'user_id',
            'outfit_id',
            'id',
            'id'
        );
    }
}
