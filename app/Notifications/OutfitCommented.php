<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class OutfitCommented extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $user;
    public $outfit;
    public $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $outfit, $comment)
    {
        $this->user = $user;
        $this->outfit = $outfit;
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        // DB 通知
        if ($notifiable->setting->inapp_comment) {
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
            'user_id' => $this->user->id,
            'outfit_id' => $this->outfit->id,
            'outfit_image' => $this->outfit->file,
            'message' => __("あなたの投稿にコメントがつきました。"),
            'created_at' => now(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'user_id' => $this->user->id,
            'outfit_id' => $this->outfit->id,
            'outfit_image' => $this->outfit->file,
            'message' => __("あなたの投稿にコメントがつきました。"),
            'created_at' => Carbon::now()->format('Y/m/d'),
            'unread_count' => $notifiable->unreadNotifications()->count(),
        ]);
    }
}
