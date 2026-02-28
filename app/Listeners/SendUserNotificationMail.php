<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Events\PasswordResetCompleted;
use App\Events\UserWithdrawn;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegisteredMail;
use App\Mail\PasswordResetCompletedMail;
use App\Mail\UserWithdrawnMail;

class SendUserNotificationMail
{
    public function handle($event)
    {
        $user = $event->user;

        // 登録完了
        if ($event instanceof UserRegistered && $user->setting->email_registration) {
            Mail::to($user->email)->queue(new UserRegisteredMail($user));
        }

        // パスワード再設定完了
        if ($event instanceof PasswordResetCompleted && $user->setting->email_password_reset) {
            Mail::to($user->email)->queue(new PasswordResetCompletedMail($user));
        }

        // 退会完了
        if ($event instanceof UserWithdrawn && $user->setting->email_withdrawal) {
            Mail::to($user->email)->queue(new UserWithdrawnMail($user));
        }
    }
}
