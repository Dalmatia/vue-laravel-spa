<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use App\Notifications\FollowedUser;

class FollowController extends Controller
{
    // フォロー状態をチェック
    public function followStatus(User $user)
    {
        $followingId = auth()->user()->id;
        $isFollowed = Follow::where('following_id', $followingId)
            ->where('followed_id', $user->id)
            ->exists();

        return response()->json(['is_followed' => $isFollowed]);
    }

    //フォローする
    public function follow(User $user)
    {
        $followingId = auth()->user()->id; // 認証ユーザーのIDを取得
        $followedId = $user->id; // フォローするユーザーのID

        if ($followingId == $followedId) {
            return response()->json(['error' => 'ユーザー自身をフォローすることはできません'], 400);
        }

        $follow = Follow::firstOrCreate([
            'following_id' => $followingId,
            'followed_id' => $followedId,
        ]);

        if ($follow->wasRecentlyCreated) {
            $user->notify(new FollowedUser(auth()->user()));
        }

        return response()->json(['success' => $follow->wasRecentlyCreated]);
    }
    //フォロー解除する
    public function unFollow(User $user)
    {
        $followingId = auth()->user()->id; // 認証ユーザーのIDを取得
        $followedId = $user->id; // フォローするユーザーのID

        if ($followingId == $followedId) {
            return response()->json(['error' => 'ユーザー自身のフォロー解除は出来ません'], 400);
        }

        $follow = Follow::where('following_id', $followingId)
            ->where('followed_id', $followedId)
            ->first();

        if ($follow) {
            $follow->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'フォローしていません'], 404);
    }

    public function follow_list(User $user)
    {
        $follow_list = $user->following()->get();
        $following_count = $user->following()->count();
        return response()->json(['follow_list' => $follow_list, 'following_count' => $following_count]);
    }

    public function follower_list(User $user)
    {
        $follower_list = $user->followers()->get();
        $follower_count = $user->followers()->count();
        return response()->json(['follower_list' => $follower_list, 'follower_count' => $follower_count]);;
    }
}
