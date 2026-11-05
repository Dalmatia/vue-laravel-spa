<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\FollowedUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationsController extends Controller
{

    public function index(User $user)
    {
        // 認証済みユーザーが自分の通知のみを取得できるようにする
        abort_unless(Auth::id() === $user->id, 403);

        // ユーザーの通知を取得
        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        $notifications->getCollection()->transform(function ($notification) {
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

        return response()->json([
            'data' => $notifications->items(),
            'has_more' => $notifications->hasMorePages(),
            'current_page' => $notifications->currentPage(),
            'last_page' => $notifications->lastPage(),
        ]);
    }

    public function unreadCount(User $user)
    {
        abort_unless(Auth::id() === $user->id, 403);
        $count = $user->unreadNotifications()->count();
        return response()->json(['unread_count' => $count]);
    }

    public function markAsRead($id)
    {
        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();
        $notification = $authenticatedUser->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => '通知が既読になりました。'], 200);
        }

        return response()->json(['message' => '通知が見つかりませんでした。'], 404);
    }

    public function destroy($id)
    {
        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();
        $notification = $authenticatedUser->notifications()->find($id);

        if ($notification) {
            $notification->delete();
            return response()->json(['message' => '通知が削除されました。'], 200);
        }

        return response()->json(['message' => '通知が見つかりませんでした。'], 404);
    }
}
