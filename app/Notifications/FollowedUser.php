<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class FollowedUser extends Notification implements ShouldBroadcast
{
    use Queueable;
    protected $follower;

    /**
     * Create a new notification instance.
     */
    public function __construct($follower)
    {
        $this->follower = $follower;
    }

    public function via(object $notifiable)
    {
        $channels = [];

        // DB 通知
        if ($notifiable->setting->inapp_follow) {
            $channels[] = 'database';
            $channels[] = 'broadcast';
        }

        // メール通知を追加するならここで分岐
        // if ($notifiable->setting->email_registration) {
        //     $channels[] = 'mail';
        // }

        return $channels;
    }

    public function toDatabase($notifiable)
    {
        return [
            'follower_name' => $this->follower->name,
            'follower_id' => $this->follower->id,
            'message' => "あなたをフォローしました。",
            'created_at' => now(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'follower_name' => $this->follower->name,
            'follower_id' => $this->follower->id,
            'message' => "あなたをフォローしました。",
            'created_at' => Carbon::now()->format('Y/m/d'),
            'unread_count' => $notifiable->unreadNotifications()->count(),
        ]);
    }
}
