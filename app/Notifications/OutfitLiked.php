<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class OutfitLiked extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $user;
    public $outfit;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $outfit)
    {
        $this->user = $user;
        $this->outfit = $outfit;
    }

    public function via(object $notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'user_id' => $this->user->id,
            'outfit_id' => $this->outfit->id,
            'message' => __("あなたの投稿にいいね!がつきました。"),
            'created_at' => now(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'user_id' => $this->user->id,
            'outfit_id' => $this->outfit->id,
            'message' => __("あなたの投稿にいいね!がつきました。"),
            'created_at' => Carbon::now()->format('Y/m/d'),
        ]);
    }
}
