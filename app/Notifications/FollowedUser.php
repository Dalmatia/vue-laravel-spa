<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

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
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'follower_name' => $this->follower->name,
            'follower_id' => $this->follower->id,
            'message' => "{$this->follower->name} さんがあなたをフォローしました。",
            'created_at' => now(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'follower_name' => $this->follower->name,
            'follower_id' => $this->follower->id,
            'message' => "{$this->follower->name} さんがあなたをフォローしました。",
        ]);
    }
}
