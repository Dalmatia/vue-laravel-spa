<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'email_registration',
        'email_password_reset',
        'email_withdrawal',
        'inapp_like',
        'inapp_comment',
        'inapp_follow',
    ];

    protected $casts = [
        'email_registration' => 'boolean',
        'email_password_reset' => 'boolean',
        'email_withdrawal' => 'boolean',
        'inapp_like' => 'boolean',
        'inapp_comment' => 'boolean',
        'inapp_follow' => 'boolean',
    ];
}
