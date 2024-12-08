<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\FollowedUser;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{

    public function index(User $user)
    {
        // ユーザーの通知を取得
        $notifications = $user->notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'message' => $notification->data['message'] ?? '通知メッセージがありません',
                'follower_id' => $notification->data['follower_id'],
                'follower_name' => $notification->data['follower_name'] ?? '不明なフォロワー',
            ];
        });

        return response()->json(['notifications' => $notifications]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'follower_id' => 'required|exists:users,id',
        ]);

        $user = User::find($validated['user_id']); // 通知を受け取るユーザー
        $follower = User::find($validated['follower_id']); // フォローしたユーザー

        // フォロー通知を送信
        $user->notify(new FollowedUser($follower));

        return response()->json(['message' => '通知が送信されました。'], 200);
    }

    public function markAsRead($id)
    {
        $notification = DatabaseNotification::find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => '通知が既読になりました。'], 200);
        }

        return response()->json(['message' => '通知が見つかりませんでした。'], 404);
    }
}
