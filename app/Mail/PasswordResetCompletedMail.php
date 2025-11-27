<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this
            ->subject('【' . config('app.name') . '】パスワード再設定が完了しました')
            ->markdown('emails.user.password_reset')
            ->with([
                'user' => $this->user,
            ]);
    }
}
