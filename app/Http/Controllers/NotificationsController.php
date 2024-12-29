<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\FollowedUser;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Carbon\Carbon;

class NotificationsController extends Controller
{

    public function index(User $user)
    {
        // ユーザーの通知を取得
        $notifications = $user->notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'message' => $notification->data['message'] ?? '通知メッセージがありません',
                'follower_id' => $notification->data['follower_id'] ?? null,
                'follower_name' => $notification->data['follower_name'] ?? null,
                'outfit_id' => $notification->data['outfit_id'] ?? null,
                'outfit_image' => $notification->data['outfit_image'] ?? null,
                'user_id' => $notification->data['user_id'] ?? null,
                'read_at' => $notification->read_at,
                'created_at' => Carbon::parse($notification->created_at)->format('Y/m/d'),
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

    public function destroy($id)
    {
        $notification = DatabaseNotification::find($id);

        if ($notification) {
            $notification->delete();
            return response()->json(['message' => '通知が削除されました。'], 200);
        }

        return response()->json(['message' => '通知が見つかりませんでした。'], 404);
    }
}
